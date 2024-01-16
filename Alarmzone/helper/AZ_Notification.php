<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_Notification.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZ_Notification
{
    #################### Protected

    /**
     * Sends a notification.
     *
     * @param string $NotificationName
     * @param string $TextPlaceholder
     * @return void
     * @throws Exception
     */
    protected function SendNotification(string $NotificationName, string $TextPlaceholder): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        $id = $this->ReadPropertyInteger('Notification');
        if ($id <= 1 || !@IPS_ObjectExists($id)) {
            return;
        }
        $notification = json_decode($this->ReadPropertyString($NotificationName), true);
        if ($notification[0]['Use']) {
            $messageText = $notification[0]['MessageText'];
            if ($notification[0]['UseTimestamp']) {
                $messageText = $messageText . ' ' . date('d.m.Y, H:i:s');
            }
            if ($TextPlaceholder != '') {
                //Check for placeholder
                if (strpos($messageText, '%1$s') !== false) {
                    $messageText = sprintf($messageText, $TextPlaceholder);
                }
            }
            $this->SendDebug(__FUNCTION__, 'Meldungstext: ' . $messageText, 0);
            //WebFront notification
            if ($notification[0]['UseWebFrontNotification']) {
                @BN_SendWebFrontNotification($id, $notification[0]['WebFrontNotificationTitle'], "\n" . $messageText, $notification[0]['WebFrontNotificationIcon'], $notification[0]['WebFrontNotificationDisplayDuration']);
            }
            //WebFront push notification
            if ($notification[0]['UseWebFrontPushNotification']) {
                //Title length max 32 characters
                $title = substr($notification[0]['WebFrontPushNotificationTitle'], 0, 32);
                //Text length max 256 characters
                $text = substr($messageText, 0, 256);
                @BN_SendWebFrontPushNotification($id, $title, "\n" . $text, $notification[0]['WebFrontPushNotificationSound'], $notification[0]['WebFrontPushNotificationTargetID']);
            }
            //E-Mail
            if ($notification[0]['UseMailer']) {
                @BN_SendMailNotification($id, $notification[0]['Subject'], "\n\n" . $messageText);
            }
            //SMS
            if ($notification[0]['UseSMS']) {
                @BN_SendNexxtMobileSMS($id, $notification[0]['SMSTitle'], "\n\n" . $messageText);
                @BN_SendSipgateSMS($id, $notification[0]['SMSTitle'], "\n\n" . $messageText);
            }
            //Telegram
            if ($notification[0]['UseTelegram']) {
                @BN_SendTelegramMessage($id, $notification[0]['TelegramTitle'], "\n\n" . $messageText);
            }
        }
    }
}