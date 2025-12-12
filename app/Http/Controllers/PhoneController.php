<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function autoDetect()
    {
        // 1. Reset ADB (Purane wireless connections kato)
        shell_exec("adb disconnect");

        // 2. Check Device
        $devices = shell_exec("adb devices");
        if (substr_count($devices, 'device') < 2) { 
            return response()->json(["status" => false, "message" => "USB Cable Not Connected."]);
        }

        // --- 3. IP FETCHING (USB Only) ---
        // Hum '-d' flag use karenge jo sirf USB device se baat karta hai
        // Taaki "More than one device" ka error na aaye.
        
        $ip = null;

        // Method 1: Samsung Special
        $ip = trim(shell_exec("adb -d shell getprop dhcp.wlan0.ipaddress"));

        // Method 2: Standard Android
        if (empty($ip)) {
            $output = shell_exec("adb -d shell ip -f inet addr show wlan0");
            preg_match('/inet\s+(\d+\.\d+\.\d+\.\d+)/', $output, $matches);
            $ip = $matches[1] ?? null;
        }

        if (empty($ip)) {
            return response()->json([
                "status" => false, 
                "message" => "IP Not Found. Ensure Phone and PC are on SAME WiFi."
            ]);
        }

        // --- 4. MODE CHANGE & CONNECT ---
        shell_exec("adb -d tcpip 5555");
        sleep(3); // Wait for mode switch

        $connectResult = shell_exec("adb connect {$ip}:5555");

        if (strpos($connectResult, 'connected') !== false) {
            return response()->json([
                "status" => true,
                "ip" => $ip,
                "message" => "Success! Connected wirelessly to $ip"
            ]);
        } else {
            return response()->json([
                "status" => false, 
                "message" => "Connection failed: $connectResult"
            ]);
        }
    }
}