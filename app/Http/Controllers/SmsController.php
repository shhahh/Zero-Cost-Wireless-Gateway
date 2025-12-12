<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsController extends Controller
{
    // --- 1. DASHBOARD (Status Checker) ---
    public function index()
    {
        // ADB Device Check command
        $output = shell_exec("adb devices");
        
        // Check karo ki list mein "device" likha hai ya nahi
        // (Hum 'List of devices attached' line ko ignore karte hain)
        $lines = explode("\n", trim($output));
        $deviceConnected = false;
        $deviceName = "No Device";

        if (count($lines) > 1) {
            foreach ($lines as $line) {
                if (strpos($line, 'device') !== false && strpos($line, 'List of') === false) {
                    $deviceConnected = true;
                    $parts = preg_split('/\s+/', $line);
                    $deviceName = $parts[0]; // Device ID
                    break;
                }
            }
        }

        return view('sms', compact('deviceConnected', 'deviceName'));
    }

    // --- 2. WIRELESS CONNECT (No Cable) ---
     public function connectWireless(Request $request)
    {
        $ip = $request->input('ip'); // Phone ka IP (e.g. 192.168.1.5)

        if (!$ip) return back()->with('error', 'IP Address Required!');

        // Step 1: TCP/IP Mode enable karo (Iske liye pehli baar USB laga hona chahiye)
        shell_exec("adb tcpip 5555");
        
        // Thoda wait
        sleep(2);

        // Step 2: Connect karo
        $result = shell_exec("adb connect $ip:5555");

        if (strpos($result, 'connected') !== false) {
            return back()->with('success', "Wireless Connection Successful! You can remove USB now.");
        } else {
            return back()->with('error', "Connection Failed: $result");
        }
    }

    // --- 3. SEND SMS (Universal Logic) ---
  public function send(Request $request)
{
    $mobile = $request->input('mobile');
    $message = $request->input('message');

    if (!$mobile || !$message) {
        return back()->with('error', 'Details missing!');
    }

    // Get PIN from env (safer than hardcoding)
    $pin = env('PHONE_PIN', '000000'); // default placeholder '000000' if env missing

    // ----------------------
    // 🔓 1. WAKE & UNLOCK PHONE (Swipe -> PIN -> Enter)
    // ----------------------

    // Wake screen
    shell_exec("adb shell input keyevent 224");
    sleep(1);

    // Swipe up to show PIN screen (adjust coords if needed)
    shell_exec("adb shell input swipe 300 1000 300 500");
    sleep(1);

    // Type PIN digit-by-digit (safe)
    // Ensure $pin contains only digits
    $pin = preg_replace('/\D/', '', $pin);
    if (empty($pin)) {
        return back()->with('error', 'PHONE_PIN is not set correctly in .env');
    }

    // Send each digit
    foreach (str_split($pin) as $digit) {
        shell_exec("adb shell input text {$digit}");
        usleep(150000); // small delay between keystrokes
    }

    // Press Enter / OK
    shell_exec("adb shell input keyevent 66");
    sleep(1);

    // ----------------------
    // 📤 2. OPEN SMS CHAT
    // ----------------------
    // Escape any double-quotes in message
    $safeMessage = str_replace('"', '\"', $message);
    $cmd = "adb shell am start -W -a android.intent.action.VIEW -d \"sms:{$mobile}\" --es \"sms_body\" \"{$safeMessage}\"";
    shell_exec($cmd);

    sleep(4);

    // ----------------------
    // 🎹 3. HIDE KEYBOARD (if appears)
    // ----------------------
    shell_exec("adb shell input keyevent 111");
    usleep(800000);

    // ----------------------
    // 📩 4. TAP SEND BUTTON
    // ----------------------
    // NOTE: Adjust X/Y for your device if needed
    $x = 965;   // change if your Send button is at different coords
    $y = 2106;  // change if your Send button is at different coords
    shell_exec("adb shell input tap $x $y");

    // Give it a moment and return
    sleep(1);

    return back()->with('success', "Message sent while phone was locked (if unlock succeeded).");
}


}