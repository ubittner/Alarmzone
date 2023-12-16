<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_Control.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZ_Control
{
    /**
     * Starts the activation by the StartActivation timer if a delayed activation is used.
     *
     * @return void
     * @throws Exception
     */
    public function StartActivation(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SetTimerInterval('StartActivation', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        $mode = $this->GetValue('Mode');
        switch ($mode) {
            //Full protection
            case 1:
                $useProtectionModeName = 'UseFullProtectionMode';
                $abortActivationNotificationName = 'FullProtectionAbortActivationNotification';
                $protectionModeName = 'FullProtectionName';
                $activationWithOpenDoorWindowNotificationName = 'FullProtectionActivationWithOpenDoorWindowNotification';
                $activationNotificationName = 'FullProtectionActivationNotification';
                break;

                //Hull protection
            case 2:
                $useProtectionModeName = 'UseHullProtectionMode';
                $abortActivationNotificationName = 'HullProtectionAbortActivationNotification';
                $protectionModeName = 'HullProtectionName';
                $activationWithOpenDoorWindowNotificationName = 'HullProtectionActivationWithOpenDoorWindowNotification';
                $activationNotificationName = 'HullProtectionActivationNotification';
                break;

                //Partial protection
            case 3:
                $useProtectionModeName = 'UsePartialProtectionMode';
                $abortActivationNotificationName = 'PartialProtectionAbortActivationNotification';
                $protectionModeName = 'PartialProtectionName';
                $activationWithOpenDoorWindowNotificationName = 'PartialProtectionActivationWithOpenDoorWindowNotification';
                $activationNotificationName = 'PartialProtectionActivationNotification';
                break;

            default:
                return;
        }
        //Check if the mode is used for this alarm zone
        if (!$this->ReadPropertyBoolean($useProtectionModeName)) {
            $this->SendDebug(__FUNCTION__, 'Der Modus ' . $this->ReadPropertyString($protectionModeName) . ' ist deaktiviert und steht nicht zur Verfügung!', 0);
            $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der Modus ' . $this->ReadPropertyString($protectionModeName) . ' ist deaktiviert und steht nicht zur Verfügung!', KL_WARNING);
            return;
        }
        //Check activation
        $activation = $this->CheckDoorWindowState($mode, true, true, false);
        $activationStateText = 'Abbruch';
        if ($activation) {
            $activationStateText = 'OK';
        }
        $this->SendDebug(__FUNCTION__, 'Aktivierung: ' . $activationStateText, 0);
        //Abort activation
        if (!$activation) {
            $this->ResetValues();
            //Protocol
            $text = 'Die Aktivierung wurde durch die Sensorenprüfung abgebrochen! (ID ' . $this->GetIDForIdent('Mode') . ')';
            $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $text;
            $this->UpdateAlarmProtocol($logText, 0);
            //Notification
            $this->SendNotification($abortActivationNotificationName, '');
            $notification = json_decode($this->ReadPropertyString($abortActivationNotificationName), true);
            if ($notification[0]['Use'] && $notification[0]['UseOpenDoorWindowNotification']) {
                IPS_Sleep(self::SLEEP_DELAY);
                $this->CheckDoorWindowState($mode, false, false, true);
            }
            //Action
            $this->ExecuteAction(0, (string) $this->InstanceID);
        }
        //Activate
        else {
            $state = 1; //armed
            $this->SetValue('AlarmZoneState', $state);
            if ($this->GetValue('DoorWindowState')) {
                $state = 3; //partial armed
            }
            $this->SetValue('AlarmZoneDetailedState', $state);
            //Protocol
            $text = $this->ReadPropertyString($protectionModeName) . ' aktiviert. (Einschaltverzögerung, ID ' . $this->GetIDForIdent('Mode') . ')';
            $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $text;
            $this->UpdateAlarmProtocol($logText, 1);
            //Notification
            if ($state == 3) { //partial armed
                //Activation with open doors and windows
                $this->SendNotification($activationWithOpenDoorWindowNotificationName, '');
                $notification = json_decode($this->ReadPropertyString($activationWithOpenDoorWindowNotificationName), true);
                if ($notification[0]['Use'] && $notification[0]['UseOpenDoorWindowNotification']) {
                    IPS_Sleep(self::SLEEP_DELAY);
                    $this->CheckDoorWindowState($mode, false, false, true);
                }
            }
            if ($state == 1) { //armed
                $this->SendNotification($activationNotificationName, '');
                $notification = json_decode($this->ReadPropertyString($activationNotificationName), true);
                if ($notification[0]['Use'] && $notification[0]['UseOpenDoorWindowNotification']) {
                    IPS_Sleep(self::SLEEP_DELAY);
                    $this->CheckDoorWindowState($mode, false, false, true);
                }
            }
            //Action
            $this->ExecuteAction($mode, (string) $this->InstanceID);
        }
    }

    /**
     * Sets an alarm.
     *
     * @param bool $State
     * false =  no alarm
     * true =   alarm
     *
     * @return void
     * @throws Exception
     */
    public function SetAlarm(bool $State): void
    {
        if (!$State) {
            if ($this->ReadPropertyBoolean('UseDisarmAlarmZoneWhenAlarmSwitchIsOff')) {
                $this->SelectProtectionMode(0, (string) $this->GetIDForIdent('AlarmSwitch'));
            } else {
                $this->SetValue('AlarmSwitch', false);
                $this->SetValue('AlarmState', 0);
                $this->SetValue('AlertingSensor', '');
                $this->SetValue('AlarmSiren', false);
                $this->SetValue('AlarmLight', false);
                $this->SetValue('AlarmCall', false);
            }
        } else {
            $alarm = false;
            $useAlarmSiren = $this->ReadPropertyBoolean('UseAlarmSirenWhenAlarmSwitchIsOn');
            $useAlarmLight = $this->ReadPropertyBoolean('UseAlarmLightWhenAlarmSwitchIsOn');
            $useAlarmCall = $this->ReadPropertyBoolean('UseAlarmCallWhenAlarmSwitchIsOn');
            if ($useAlarmSiren || $useAlarmLight || $useAlarmCall) {
                $alarm = true;
            }
            if ($alarm) {
                $this->SetValue('AlarmSwitch', true);
                $this->SetValue('AlarmState', 1);
                $this->SetValue('AlertingSensor', $this->ReadPropertyString('AlertingSensorNameWhenAlarmSwitchIsOn'));
                if ($useAlarmSiren) {
                    $this->SetValue('AlarmSiren', true);
                }
                if ($useAlarmLight) {
                    $this->SetValue('AlarmLight', true);
                }
                if ($useAlarmCall) {
                    $this->SetValue('AlarmCall', true);
                }
            }
        }
    }

    /**
     * Selects the protection mode.
     *
     * @param int $Mode
     * 0 =  disarmed,
     * 1 =  full protection mode,
     * 2 =  hull protection mode,
     * 3 =  partial protection mode
     *
     * @param string $SenderID
     *
     * @return bool
     * false =  An error occurred
     * true =   Successful
     *
     * @throws Exception
     */
    public function SelectProtectionMode(int $Mode, string $SenderID): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        switch ($Mode) {
            case 0:
                $modeText = $this->ReadPropertyString('DisarmedName');
                break;

            case 1:
                $modeText = $this->ReadPropertyString('FullProtectionName');
                break;

            case 2:
                $modeText = $this->ReadPropertyString('HullProtectionName');
                break;

            case 3:
                $modeText = $this->ReadPropertyString('PartialProtectionName');
                break;

            default:
                $modeText = 'Unbekannt';
        }
        $this->SendDebug(__FUNCTION__, 'Modus: ' . $modeText, 0);
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        switch ($Mode) {
            //Disarm
            case 0:
                $this->ResetValues();
                $this->SetTimerInterval('StartActivation', 0);
                //Protocol
                $text = $this->ReadPropertyString('SystemName') . ' deaktiviert. (ID ' . $SenderID . ', ID ' . $this->GetIDForIdent('Mode') . ')';
                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $text;
                $this->UpdateAlarmProtocol($logText, 1);
                //Notification
                $this->SendNotification('DeactivationNotification', '');
                $this->CheckDoorWindowState($Mode, false, false, false);
                //Action
                $this->ExecuteAction(0, $SenderID);
                return true;

                //Full protection mode
            case 1:
                $fullProtectionControlSwitch = true;
                $hullProtectionControlSwitch = false;
                $partialProtectionControlSwitch = false;
                $useProtectionModeName = 'UseFullProtectionMode';
                $activationDelayName = 'FullProtectionModeActivationDelay';
                $protectionModeName = 'FullProtectionName';
                $delayedActivationNotificationName = 'FullProtectionDelayedActivationNotification';
                $abortActivationNotificationName = 'FullProtectionAbortActivationNotification';
                $activationNotificationName = 'FullProtectionActivationNotification';
                $activationWithOpenDoorWindowNotificationName = 'FullProtectionActivationWithOpenDoorWindowNotification';
                break;

                //Hull protection mode
            case 2:
                $fullProtectionControlSwitch = false;
                $hullProtectionControlSwitch = true;
                $partialProtectionControlSwitch = false;
                $useProtectionModeName = 'UseHullProtectionMode';
                $activationDelayName = 'HullProtectionModeActivationDelay';
                $protectionModeName = 'HullProtectionName';
                $delayedActivationNotificationName = 'HullProtectionDelayedActivationNotification';
                $abortActivationNotificationName = 'HullProtectionAbortActivationNotification';
                $activationNotificationName = 'HullProtectionActivationNotification';
                $activationWithOpenDoorWindowNotificationName = 'HullProtectionActivationWithOpenDoorWindowNotification';
                break;

                //Partial protection mode
            case 3:
                $fullProtectionControlSwitch = false;
                $hullProtectionControlSwitch = false;
                $partialProtectionControlSwitch = true;
                $useProtectionModeName = 'UsePartialProtectionMode';
                $activationDelayName = 'PartialProtectionModeActivationDelay';
                $protectionModeName = 'PartialProtectionName';
                $delayedActivationNotificationName = 'PartialProtectionDelayedActivationNotification';
                $abortActivationNotificationName = 'PartialProtectionAbortActivationNotification';
                $activationNotificationName = 'PartialProtectionActivationNotification';
                $activationWithOpenDoorWindowNotificationName = 'PartialProtectionActivationWithOpenDoorWindowNotification';
                break;

            default:
                return false;
        }
        //Check if the mode is used for this alarm zone
        if (!$this->ReadPropertyBoolean($useProtectionModeName)) {
            $this->SendDebug(__FUNCTION__, 'Der Modus ' . $modeText . ' ist deaktiviert und steht nicht zur Verfügung!', 0);
            $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der Modus ' . $modeText . ' ist deaktiviert und steht nicht zur Verfügung!', KL_WARNING);
            return false;
        }
        $result = true;
        $this->SetValue('FullProtectionControlSwitch', $fullProtectionControlSwitch);
        $this->SetValue('HullProtectionControlSwitch', $hullProtectionControlSwitch);
        $this->SetValue('PartialProtectionControlSwitch', $partialProtectionControlSwitch);
        $this->SetValue('Mode', $Mode);
        $this->SetValue('AlarmState', 0);
        $this->SetValue('AlertingSensor', '');
        $this->SetValue('AlarmSiren', false);
        $this->SetValue('AlarmLight', false);
        $this->SetValue('AlarmCall', false);
        $this->ResetBlacklist();
        //Check activation delay
        $activationDelay = $this->ReadPropertyInteger($activationDelayName);
        if ($activationDelay > 0) {
            //Check actual state only
            $this->CheckDoorWindowState($Mode, false, false, false);
            //Activate timer, timer will execute the StartActivation methode
            $this->SetTimerInterval('StartActivation', $activationDelay * 1000);
            $stateValue = 2; //delayed armed
            $this->SetValue('AlarmZoneState', $stateValue);
            if ($this->GetValue('DoorWindowState')) {
                $stateValue = 4; //delayed partial armed
            }
            $this->SetValue('AlarmZoneDetailedState', $stateValue);
            //Protocol
            $text = $this->ReadPropertyString($protectionModeName) . ' wird in ' . $activationDelay . ' Sekunden automatisch aktiviert. (ID ' . $SenderID . ', ID ' . $this->GetIDForIdent('Mode') . ')';
            $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $text;
            $this->UpdateAlarmProtocol($logText, 0);
            //Notification
            $this->SendNotification($delayedActivationNotificationName, (string) $activationDelay);
            $notification = json_decode($this->ReadPropertyString($delayedActivationNotificationName), true);
            //Action
            $this->ExecuteAction($Mode, $SenderID);
        }
        //Immediate activation
        else {
            $activation = $this->CheckDoorWindowState($Mode, false, false, false);
            //Abort activation
            if (!$activation) {
                $result = false;
                $this->ResetValues();
                $this->SetTimerInterval('StartActivation', 0);
                //Protocol
                $text = 'Die Aktivierung wurde durch die Sensorenprüfung abgebrochen! (ID ' . $this->GetIDForIdent('Mode') . ')';
                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $text;
                $this->UpdateAlarmProtocol($logText, 0);
                //Notification
                $this->SendNotification($abortActivationNotificationName, '');
                $notification = json_decode($this->ReadPropertyString($abortActivationNotificationName), true);
                //Action
                $this->ExecuteAction(0, $SenderID);
            }
            //Activate
            else {
                $this->CheckDoorWindowState($Mode, true, true, false); //adds a sensor of an open door or window to the blacklist
                $state = 1; //armed
                $this->SetValue('AlarmZoneState', $state);
                $doorWindowState = $this->GetValue('DoorWindowState');
                if ($doorWindowState) {
                    $state = 3; //partial armed
                }
                $this->SetValue('AlarmZoneDetailedState', $state);
                //Protocol
                $text = $this->ReadPropertyString($protectionModeName) . ' aktiviert. (ID ' . $SenderID . ', ID ' . $this->GetIDForIdent('Mode') . ')';
                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $text;
                $this->UpdateAlarmProtocol($logText, 1);
                //Notification
                if (!$doorWindowState) { //Closed
                    $this->SendNotification($activationNotificationName, '');
                    $notification = json_decode($this->ReadPropertyString($activationNotificationName), true);
                } else { //Opened
                    $this->SendNotification($activationWithOpenDoorWindowNotificationName, '');
                    $notification = json_decode($this->ReadPropertyString($activationWithOpenDoorWindowNotificationName), true);
                }
                //Action
                $this->ExecuteAction($Mode, $SenderID);
            }
        }
        if ($notification[0]['Use'] && $notification[0]['UseOpenDoorWindowNotification']) {
            IPS_Sleep(self::SLEEP_DELAY);
            $this->CheckDoorWindowState($Mode, false, false, true);
        }
        return $result;
    }

    #################### Private

    /**
     * Resets the values of the alarm zone.
     *
     * @return void
     * @throws Exception
     */
    private function ResetValues(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->ResetBlacklist();
        $this->SetValue('AlarmSwitch', false);
        $this->SetValue('AlertingSensor', '');
        $this->SetValue('FullProtectionControlSwitch', false);
        $this->SetValue('HullProtectionControlSwitch', false);
        $this->SetValue('PartialProtectionControlSwitch', false);
        $this->SetValue('Mode', 0);
        $this->SetValue('AlarmZoneState', 0);
        $this->SetValue('AlarmZoneDetailedState', 0);
        $this->SetValue('AlarmState', 0);
        $this->SetValue('AlarmSiren', false);
        $this->SetValue('AlarmLight', false);
        $this->SetValue('AlarmCall', false);
    }

    /**
     * Executes an action.
     *
     * @param int $Mode
     * 0 =  disarmed,
     * 1 =  full protection,
     * 2 =  hull protection,
     * 3 =  partial protection
     *
     * @param string $SenderID
     * ID of the sender
     *
     * @return void
     * @throws Exception
     */
    private function ExecuteAction(int $Mode, string $SenderID): void
    {
        $executeAction = false;
        $action = [];
        switch ($Mode) {
            case 0: # disarmed
                switch ($SenderID) {
                    case $this->InstanceID:
                    case $this->GetIDForIdent('AlarmSwitch'):
                    case $this->GetIDForIdent('FullProtectionControlSwitch'):
                    case $this->GetIDForIdent('HullProtectionControlSwitch'):
                    case $this->GetIDForIdent('PartialProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UseDisarmedAction')) {
                            $executeAction = true;
                            $action = json_decode($this->ReadPropertyString('DisarmedAction'), true);
                        }
                        break;

                }
                break;

            case 1: # full protection
                switch ($SenderID) {
                    case $this->InstanceID:
                    case $this->GetIDForIdent('FullProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UseFullProtectionAction')) {
                            //Check if the status has remained the same
                            if ($this->GetValue('FullProtectionControlSwitch') || $this->GetValue('Mode') == 1) {
                                $executeAction = true;
                                $action = json_decode($this->ReadPropertyString('FullProtectionAction'), true);
                            }
                        }
                        break;

                }
                break;

            case 2: # hull protection
                switch ($SenderID) {
                    case $this->InstanceID:
                    case $this->GetIDForIdent('HullProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UseHullProtectionAction')) {
                            //Check if the status has remained the same
                            if ($this->GetValue('HullProtectionControlSwitch') || $this->GetValue('Mode') == 2) {
                                $executeAction = true;
                                $action = json_decode($this->ReadPropertyString('HullProtectionAction'), true);
                            }
                        }
                        break;

                }
                break;

            case 3: # partial protection
                switch ($SenderID) {
                    case $this->InstanceID:
                    case $this->GetIDForIdent('PartialProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UsePartialProtectionAction')) {
                            //Check if the status has remained the same
                            if ($this->GetValue('PartialProtectionControlSwitch') || $this->GetValue('Mode') == 3) {
                                $executeAction = true;
                                $action = json_decode($this->ReadPropertyString('PartialProtectionAction'), true);
                            }
                        }
                        break;

                }
                break;

        }
        if ($executeAction && !empty($action)) {
            $this->SendDebug(__FUNCTION__, 'Aktion: ' . json_encode($action), 0);
            IPS_RunAction($action['actionID'], $action['parameters']);
        }
    }
}