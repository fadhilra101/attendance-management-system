<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Office;
use App\Models\QrCode;
use App\Models\Holiday;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AttendanceController extends Controller
{
    // Maximum allowed distance in meters
    const MAX_DISTANCE = 100;

    public function redirectToApi(Request $request)
    {
        $qr_code = QrCode::find($request->qr_code_id);
        $office = Office::find($request->office_id);
        $type = $request->type;

        if (!$qr_code || !$office) {
            return response()->json(['error' => 'Invalid QR Code or Office'], 400);
        }

        return view('redirect-api', compact('qr_code', 'office', 'type'));
    }

    public function processScan(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'type' => 'required|in:check_in,check_out',
            'office_id' => 'required|exists:offices,id',
            'qr_code_id' => 'required|exists:qr_codes,id',
        ])->validate();

        $qrCode = QrCode::find($validated['qr_code_id']);
        $office = Office::find($validated['office_id']);

        if (!$qrCode) {
            return response()->json(['error' => 'Invalid QR Code'], 400);
        }

        // Check if QR code is expired (2 minutes validity)
        if (Carbon::parse($qrCode->created_at)->addMinutes(2)->isPast()) {
            return response()->json(['error' => 'QR Code has expired'], 400);
        }

        // Delete QR code after use
        $qrCode->delete();

        // Verify location
        if (!$this->determineLocation($validated['lat'], $validated['lng'], $office)) {
            return response()->json(['error' => 'You are not within office premises'], 400);
        }

        $user = Auth::user();
        $today = Carbon::today();
        $currentTime = Carbon::now();

        // Find existing attendance for today
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();

        if ($validated['type'] === 'check_in') {
            if ($attendance && $attendance->check_in_time) {
                return redirect()->route('scan')->with('error', 'Already checked in for today');
            }
            return $this->processCheckIn($user, $office, $qrCode, $currentTime, $attendance);
        } else {
            if (!$attendance) {
                return redirect()->route('scan')->with('error', 'Must check in first');
            }
            if ($attendance->check_out_time) {
                return redirect()->route('scan')->with('error', 'Already checked out for today');
            }
            return $this->processCheckOut($attendance, $qrCode, $currentTime);
        }
    }

    private function processCheckIn($user, $office, $qrCode, $currentTime, $attendance)
    {
        $status = $this->determineStatus($currentTime, $user->role->entry_hour, 'check_in');

        if ($attendance) {
            $attendance->update([
                'check_in_time' => $currentTime,
                'status' => $status,
            ]);
        } else {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'office_id' => $office->id,
                'check_in_time' => $currentTime,
                'status' => $status,
            ]);
        }

        return redirect()->route('scan')->with('success', 'Check-in successful');
    }

    private function processCheckOut($attendance, $qrCode, $currentTime)
    {
        $status = $this->determineStatus($currentTime, $attendance->user->role->exit_hour, 'check_out');

        // Always update the status for checkout regardless of type
        $attendance->status = $status;
        $attendance->check_out_time = $currentTime;
        $attendance->save();

        return redirect()->route('scan')->with('success', 'Check-out successful');
    }

    private function determineLocation($userLat, $userLng, $office)
    {
        // Calculate distance using Haversine formula
        $earthRadius = 6371000; // Earth's radius in meters

        $latFrom = deg2rad($userLat);
        $lonFrom = deg2rad($userLng);
        $latTo = deg2rad($office->gps_lat);
        $lonTo = deg2rad($office->gps_lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        $distance = $angle * $earthRadius;

        return $distance <= self::MAX_DISTANCE;
    }

    private function determineStatus($currentTime, $scheduleTime, $type)
    {
        $scheduleDateTime = Carbon::parse($scheduleTime);
        $currentDateTime = Carbon::parse($currentTime);

        // Check if today is a holiday
        if (Holiday::isHoliday($currentDateTime)) {
            return Attendance::STATUS_HOLIDAY;
        }

        if ($type === 'check_in') {
            $gracePeriod = $scheduleDateTime->copy()->addMinutes(15);
            return $currentDateTime <= $gracePeriod ?
                Attendance::STATUS_PRESENT :
                Attendance::STATUS_LATE;
        }

        // For checkout, combine the statuses
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('check_in_time', Carbon::today())
            ->first();

        $isLateArrival = Carbon::parse($attendance->check_in_time)
            ->gt($scheduleDateTime->copy()->addMinutes(15));

        if ($currentDateTime < $scheduleDateTime) {
            return $isLateArrival ?
                Attendance::STATUS_LATE_AND_EARLY :
                Attendance::STATUS_ONTIME_AND_EARLY;
        }

        if ($currentDateTime > $scheduleDateTime->copy()->addHours(2)) {
            return $isLateArrival ?
                Attendance::STATUS_LATE_AND_OVERTIME :
                Attendance::STATUS_ONTIME_AND_OVERTIME;
        }

        return $isLateArrival ?
            Attendance::STATUS_LATE :
            Attendance::STATUS_ONTIME_AND_NORMAL;
    }
}
