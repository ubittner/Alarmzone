<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/helper/
 * @file          AZ_Notification.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZST_Notification
{
    #################### Public

    /**
     * Executes the notification from the alarm zone controller.
     *
     * If the alarm zone! uses an activation check or has a delayed activation,
     * all notifications must be done by the alarm zone itself!
     *
     * @param int $Mode
     * 0 =      Disarmed,
     * 1 =      Full protection mode,
     * 2 =      Hull protection mode,
     * 3 =      Partial protection mode,
     * 4 =      Individual protection mode
     * 911 =    Panic alarm
     *
     * @return void
     * @throws Exception
     */
    public function ExecuteAlarmZoneControllerNotification(int $Mode): void
    {
        $notifcationName = '';
        $textPlaceholder = '';
        switch ($Mode) {
            case 0: //Disarm
                $notifcationName = 'DeactivationNotification';
                break;

            case 1: //Full protection mode
                if ($this->ReadPropertyBoolean('FullProtectionActivationCheck') || $this->ReadPropertyBoolean('FullProtectionActivationDelay')) {
                    break;
                }
                //Armed with an open door and/or open window
                $name = 'FullProtectionActivationWithOpenDoorWindowNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    if ($this->GetValue('DoorWindowState')) {
                        $notifcationName = $name;
                        break;
                    }
                }
                //Armed
                $name = 'FullProtectionActivationNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    $notifcationName = $name;
                    break;
                }
                break;

            case 2: //Hull protection mode
                if ($this->ReadPropertyBoolean('HullProtectionActivationCheck') || $this->ReadPropertyBoolean('HullProtectionActivationDelay')) {
                    break;
                }
                //Armed with an open door and/or open window
                $name = 'HullProtectionActivationWithOpenDoorWindowNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    if ($this->GetValue('DoorWindowState')) {
                        $notifcationName = $name;
                        break;
                    }
                }
                //Armed
                $name = 'HullProtectionActivationNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    $notifcationName = $name;
                    break;
                }
                break;

            case 3: //Partial protection mode
                if ($this->ReadPropertyBoolean('PartialProtectionActivationCheck') || $this->ReadPropertyBoolean('PartialProtectionActivationDelay')) {
                    break;
                }
                //Armed with an open door and/or open window
                $name = 'PartialProtectionActivationWithOpenDoorWindowNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    if ($this->GetValue('DoorWindowState')) {
                        $notifcationName = $name;
                        break;
                    }
                }
                //Armed
                $name = 'PartialProtectionActivationNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    $notifcationName = $name;
                    break;
                }
                break;

            case 4: //Individual protection mode
                if ($this->ReadPropertyBoolean('IndividualProtectionActivationCheck') || $this->ReadPropertyBoolean('IndividualProtectionActivationDelay')) {
                    break;
                }
                //Armed with an open door and/or open window
                $name = 'IndividualProtectionActivationWithOpenDoorWindowNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    if ($this->GetValue('DoorWindowState')) {
                        $notifcationName = $name;
                        break;
                    }
                }
                //Armed
                $name = 'IndividualProtectionActivationNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    $notifcationName = $name;
                    break;
                }
                break;

            case 911: //Panik alarm
                $name = 'PanicAlarmNotification';
                $notification = json_decode($this->ReadPropertyString($name), true);
                if ($notification[0]['Use']) {
                    $notifcationName = $name;
                    $textPlaceholder = $this->ReadPropertyString('AlertingSensorNameWhenAlarmSwitchIsOn');
                    break;
                }
                break;

        }
        if ($notifcationName != '') {
            $notification = json_decode($this->ReadPropertyString($notifcationName), true);
            if ($notification[0]['Use']) {
                $this->SendNotification($notifcationName, $textPlaceholder);
            }
        }
    }

    #################### Protected

    /**
     * Checks whether the alarm zone controller should use a notification.
     *
     * If the alarm zone! uses an activation check or has a delayed activation,
     * all notifications must be done by the alarm zone itself!
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection mode,
     * 2 =  Hull protection mode,
     * 3 =  Partial protection mode,
     * 4 =  Individual protection mode
     *
     * @return bool
     * false =  don't use notification,
     * true =   use notification
     *
     * @throws Exception
     */
    protected function CheckAlarmZoneControllerNotification(int $Mode): bool
    {
        $useAlarmZoneControllerNotification = false;
        $activationCheck = false;
        $activationDelay = false;
        $notifications = [];
        switch ($Mode) {
            case 0: //Disarm
                $notifications = ['DeactivationNotification'];
                break;

            case 1: //Full protection mode
                $activationCheck = $this->ReadPropertyBoolean('FullProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('FullProtectionActivationDelay');
                $notifications = [
                    'FullProtectionActivationWithOpenDoorWindowNotification',
                    'FullProtectionActivationNotification'];
                break;

            case 2: //Hull protection mode
                $activationCheck = $this->ReadPropertyBoolean('HullProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('HullProtectionActivationDelay');
                $notifications = [
                    'HullProtectionActivationWithOpenDoorWindowNotification',
                    'HullProtectionActivationNotification'];
                break;

            case 3: //Partial protection mode
                $activationCheck = $this->ReadPropertyBoolean('PartialProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('PartialProtectionActivationDelay');
                $notifications = [
                    'PartialProtectionActivationWithOpenDoorWindowNotification',
                    'PartialProtectionActivationNotification'];
                break;

            case 4: //Individual protection mode
                $activationCheck = $this->ReadPropertyBoolean('IndividualProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('IndividualProtectionActivationDelay');
                $notifications = [
                    'IndividualProtectionActivationWithOpenDoorWindowNotification',
                    'IndividualProtectionActivationNotification'];
                break;

        }

        if ($activationCheck || $activationDelay) {
            return false;
        }
        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                $notification = json_decode($this->ReadPropertyString($notification), true);
                if ($notification[0]['Use']) {
                    $useAlarmZoneControllerNotification = true;
                }
            }
        }
        return $useAlarmZoneControllerNotification;
    }

    #################### Private

    /**
     * Sends a notification.
     *
     * @param string $NotificationName
     * @param string $TextPlaceholder
     * @return void
     * @throws Exception
     */
    private function SendNotification(string $NotificationName, string $TextPlaceholder): void
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
            //Tile visualisation notification
            if (array_key_exists('UseTileVisualisationNotification', $notification[0])) {
                if ($notification[0]['UseTileVisualisationNotification']) {
                    if (array_key_exists('TileVisualisationNotificationTitle', $notification[0])) {
                        //Title length max 32 characters
                        $title = substr($notification[0]['TileVisualisationNotificationTitle'], 0, 32);
                    }
                    //Text length max 256 characters
                    $text = substr($messageText, 0, 256);
                    //Icon
                    if (array_key_exists('TileVisualisationNotificationIcon', $notification[0])) {
                        $icon = $notification[0]['TileVisualisationNotificationIcon'];
                    }
                    //Sound
                    if (array_key_exists('TileVisualisationNotificationSound', $notification[0])) {
                        $sound = $notification[0]['TileVisualisationNotificationSound'];
                    }
                    //Target
                    if (array_key_exists('TileVisualisationNotificationTargetID', $notification[0])) {
                        $target = $notification[0]['TileVisualisationNotificationTargetID'];
                    }
                    if (isset($title) && isset($icon) && isset($sound) && isset($target)) {
                        @BN_PostNotification($id, $title, "\n" . $text, $icon, $sound, $target);
                    }
                }
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