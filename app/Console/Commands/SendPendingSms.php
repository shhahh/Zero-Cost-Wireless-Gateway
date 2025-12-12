<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmsQueue;

class SendPendingSms extends Command
{
    // Command ka naam (Jo terminal m chalega)
    protected $signature = 'sms:process';
    protected $description = 'Send pending SMS via ADB Phone';

    public function handle()
    {
        $this->info("Checking for pending SMS...");

        // 1. Pending messages uthao (Ek baar mein 5 taaki phone hang na ho)
        $pendingSms = SmsQueue::where('status', 'pending')->take(5)->get();

        if ($pendingSms->isEmpty()) {
            $this->info("No pending SMS.");
            return;
        }

        foreach ($pendingSms as $sms) {
            $this->info("Sending to: " . $sms->mobile);

            // --- WAHI ADB LOGIC ---
            // 1. Phone Jagaao
            shell_exec("adb shell input keyevent 224");
            shell_exec("adb shell input keyevent 82");

            // 2. App Kholo & Message Type Karo
            $cmd = "adb shell am start -W -a android.intent.action.VIEW -d \"sms:{$sms->mobile}\" --es \"sms_body\" \"{$sms->message}\"";
            shell_exec($cmd);
            
            sleep(4); // Wait

            // 3. Keyboard Hatao & Send
            shell_exec("adb shell input keyevent 111"); // Escape keyboard
            usleep(800000);
            
            // Apne Coordinates use karo (965, 2106)
            shell_exec("adb shell input tap 965 2106");

            // 4. Database Update Karo
            $sms->status = 'sent';
            $sms->save();

            $this->info("Sent Successfully!");
            
            // Thoda break do agle message se pehle
            sleep(2);
        }
    }
}