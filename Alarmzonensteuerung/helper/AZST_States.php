<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/helper/
 * @file          AZST_States.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZST_States
{
    /**
     * Updates to the actual states.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateStates(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $result = true;
        $update1 = $this->UpdateProtectionMode();
        $update2 = $this->UpdateGlassBreakageDetectorControl();
        $update3 = $this->UpdateSystemState();
        $update4 = $this->UpdateSystemDetailedState();
        $update5 = $this->UpdateAlarmState();
        $update6 = $this->UpdateAlertingSensor();
        $update7 = $this->UpdateDoorWindowState();
        $update8 = $this->UpdateMotionDetectorState();
        $update9 = $this->UpdateGlassBreakageDetectorState();
        $update10 = $this->UpdateSmokeDetectorState();
        $update11 = $this->UpdateWaterDetectorState();
        $update12 = $this->UpdateAlarmSiren();
        $update13 = $this->UpdateAlarmLight();
        $update14 = $this->UpdateAlarmCall();
        $update15 = $this->UpdatePanicAlarm();
        if (!$update1 ||
            !$update2 ||
            !$update3 ||
            !$update4 ||
            !$update5 ||
            !$update6 ||
            !$update7 ||
            !$update8 ||
            !$update9 ||
            !$update10 ||
            !$update11 ||
            !$update12 ||
            !$update13 ||
            !$update14 ||
            !$update15) {
            $result = false;
        }
        return $result;
    }

    /**
     * Updates the status of the protection modes.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateProtectionMode(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        if ($this->ReadAttributeBoolean('DisableUpdateMode')) {
            return false;
        }
        $result = false;
        $zoneStates = [];
        $amount = 0;
        $zoneStates['Disarmed'] = 0;
        $zoneStates['fullProtectionMode'] = 0;
        $zoneStates['hullProtectionMode'] = 0;
        $zoneStates['partialProtectionMode'] = 0;
        $variables = json_decode($this->ReadPropertyString('ProtectionMode'), true);
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $amount++;
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $value = GetValue($variable['ID']);
                    switch ($value) {
                        case 0: //Disarmed
                            $zoneStates['Disarmed'] += 1;
                            break;

                        case 1: //Full protection mode
                            $zoneStates['fullProtectionMode'] += 1;
                            break;

                        case 2: //Hull protection mode
                            $zoneStates['hullProtectionMode'] += 1;
                            break;

                        case 3: //Partial protection mode
                            $zoneStates['partialProtectionMode'] += 1;
                            break;

                    }
                }
            }
        }
        if ($amount >= 1) {
            $result = true;
            //individual protection mode
            $mode = 4;
            $stateText = 'Individualschutz, alle Alarmzonen unterschiedlich';
            if ($zoneStates['Disarmed'] == $amount) {
                //All zones are off
                $mode = 0;
                $stateText = 'Alle Alarmzonen unscharf';
            }
            if ($zoneStates['fullProtectionMode'] == $amount) {
                //Only full protection mode is enabled
                $mode = 1;
                $stateText = 'Vollschutz aller Alarmzonen';
            }
            if ($zoneStates['hullProtectionMode'] == $amount) {
                //Only hull protection mode is enabled
                $mode = 2;
                $stateText = 'Hüllschutz aller Alarmzonen';
            }
            if ($zoneStates['partialProtectionMode'] == $amount) {
                //Only partial protection mode is enabled
                $mode = 3;
                $stateText = 'Teilschutz aller Alarmzonen';
            }
            //Control switches
            switch ($mode) {
                case 0: //disarmed
                    $this->SetValue('FullProtectionControlSwitch', false);
                    $this->SetValue('HullProtectionControlSwitch', false);
                    $this->SetValue('PartialProtectionControlSwitch', false);
                    $this->SetValue('IndividualProtectionControlSwitch', false);
                    break;

                case 1: //full protection
                    $this->SetValue('FullProtectionControlSwitch', true);
                    $this->SetValue('HullProtectionControlSwitch', false);
                    $this->SetValue('PartialProtectionControlSwitch', false);
                    $this->SetValue('IndividualProtectionControlSwitch', false);
                    break;

                case 2: //hull protection
                    $this->SetValue('FullProtectionControlSwitch', false);
                    $this->SetValue('HullProtectionControlSwitch', true);
                    $this->SetValue('PartialProtectionControlSwitch', false);
                    $this->SetValue('IndividualProtectionControlSwitch', false);
                    break;

                case 3: //partial protection
                    $this->SetValue('FullProtectionControlSwitch', false);
                    $this->SetValue('HullProtectionControlSwitch', false);
                    $this->SetValue('PartialProtectionControlSwitch', true);
                    $this->SetValue('IndividualProtectionControlSwitch', false);
                    break;

                case 4: //individual protection
                    $this->SetValue('FullProtectionControlSwitch', false);
                    $this->SetValue('HullProtectionControlSwitch', false);
                    $this->SetValue('PartialProtectionControlSwitch', false);
                    $this->SetValue('IndividualProtectionControlSwitch', true);
                    break;

            }
            //Mode
            $this->SetValue('Mode', $mode);
            //Status indicator
            $this->ExecuteStatusIndicator($mode);
            //Debug
            $this->SendDebug(__FUNCTION__, 'Modus: ' . $mode . ' = ' . $stateText, 0);
        }
        return $result;
    }

    /**
     * Updates the state of the glass breakage detector control.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateGlassBreakageDetectorControl(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('GlassBreakageDetectorControl'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //on
                    }
                }
            }
        }
        $this->SetValue('GlassBreakageDetectorControlSwitch', $state);
        return $result;
    }

    /**
     * Updates the state of the system state.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateSystemState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        if ($this->ReadAttributeBoolean('DisableUpdateMode')) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('SystemState'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $zoneStates = [];
        $zoneStates['disarmed'] = 0;
        $zoneStates['armed'] = 0;
        $zoneStates['delayedArmed'] = 0;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    switch (GetValueInteger($variable['ID'])) {
                        case 0: //disarmed
                            $zoneStates['disarmed'] += 1;
                            break;

                        case 1: //armed
                            $zoneStates['armed'] += 1;
                            break;

                        case 2: //delayed armed
                            $zoneStates['delayedArmed'] += 1;
                            break;

                    }
                }
            }
        }
        $state = 0; //disarmed
        if ($zoneStates['armed'] > 0) {
            $state = 1; //armed
        }
        if ($zoneStates['delayedArmed'] > 0) {
            $state = 2; //delayed armed
        }
        $this->SetValue('SystemState', $state);
        return $result;
    }

    /**
     * Updates the state of the system detailed state.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateSystemDetailedState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        if ($this->ReadAttributeBoolean('DisableUpdateMode')) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('SystemDetailedState'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $amount = 0;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $amount++;
            }
        }
        $zoneStates = [];
        $zoneStates['disarmed'] = 0;
        $zoneStates['armed'] = 0;
        $zoneStates['delayedArmed'] = 0;
        $zoneStates['partialArmed'] = 0;
        $zoneStates['delayedPartialArmed'] = 0;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    switch (GetValueInteger($variable['ID'])) {
                        case 0: //disarmed
                            $zoneStates['disarmed'] += 1;
                            break;

                        case 1: //armed
                            $zoneStates['armed'] += 1;
                            break;

                        case 2: //delayed armed
                            $zoneStates['delayedArmed'] += 1;
                            break;

                        case 3: //partial armed
                            $zoneStates['partialArmed'] += 1;
                            break;

                        case 4: //delayed partial armed
                            $zoneStates['delayedPartialArmed'] += 1;
                            break;

                    }
                }
            }
        }
        $state = 0; //disarmed
        if ($zoneStates['armed'] > 0) {
            if ($zoneStates['armed'] == $amount) {
                $state = 1; //armed
            } else {
                $state = 3; //partial armed
            }
        }
        if ($zoneStates['delayedArmed'] > 0) {
            $state = 2; //delayed armed
        }
        if ($zoneStates['partialArmed'] > 0) {
            $state = 3; //partial armed
        }
        if ($zoneStates['delayedPartialArmed'] > 0) {
            $state = 4; //delayed partial armed
        }
        $this->SetValue('SystemDetailedState', $state);
        return $result;
    }

    /**
     * Updates the state of the alarm state.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateAlarmState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('AlarmState'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = 0;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueInteger($variable['ID']);
                    if ($actualValue == 1) {
                        $state = 1; //alarm
                    }
                }
            }
        }
        $this->SetValue('AlarmState', $state);
        if ($state == 0) {
            $this->SetValue('AlarmSwitch', false);
        }
        if ($state == 1) {
            $this->SetValue('AlarmSwitch', true);
        }
        return $result;
    }

    /**
     * Updates the state of the alerting sensor.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateAlertingSensor(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $result = false;
        $sensorName = '';
        $variables = json_decode($this->ReadPropertyString('AlertingSensor'), true);
        if (!empty($variables)) {
            foreach ($variables as $variable) {
                if ($variable['Use']) {
                    $id = $variable['ID'];
                    if ($id > 1 && @IPS_ObjectExists($id)) {
                        $result = true;
                        $actualValue = GetValueString($id);
                        if ($actualValue != '') {
                            $sensorName = $actualValue;
                        }
                    }
                }
            }
        }
        $this->SetValue('AlertingSensor', $sensorName);
        return $result;
    }

    /**
     * Updates the state of the door and window state.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateDoorWindowState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('DoorWindowState'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //open
                    }
                }
            }
        }
        $this->SetValue('DoorWindowState', $state);
        return $result;
    }

    /**
     * Updates the state of the motion detector state.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateMotionDetectorState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('MotionDetectorState'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //motion detected
                    }
                }
            }
        }
        $this->SetValue('MotionDetectorState', $state);
        return $result;
    }

    /**
     * Updates the glass breakage detector state.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateGlassBreakageDetectorState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('GlassBreakageDetectorState'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //glass breakage detected
                    }
                }
            }
        }
        $this->SetValue('GlassBreakageDetectorState', $state);
        return $result;
    }

    /**
     * Updates the state of the smoke detector state.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateSmokeDetectorState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('SmokeDetectorState'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //smoke detected
                    }
                }
            }
        }
        $this->SetValue('SmokeDetectorState', $state);
        return $result;
    }

    /**
     * Updates the state of the water detector state.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateWaterDetectorState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('WaterDetectorState'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //water detected
                    }
                }
            }
        }
        $this->SetValue('WaterDetectorState', $state);
        return $result;
    }

    /**
     * Updates the state of the alarm siren.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateAlarmSiren(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('AlarmSiren'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //on
                    }
                }
            }
        }
        $this->SetValue('AlarmSiren', $state);
        return $result;
    }

    /**
     * Updates the state of the alarm light.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateAlarmLight(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('AlarmLight'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //on
                    }
                }
            }
        }
        $this->SetValue('AlarmLight', $state);
        return $result;
    }

    /**
     * Updates the state of the alarm call.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdateAlarmCall(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('AlarmCall'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //on
                    }
                }
            }
        }
        $this->SetValue('AlarmCall', $state);
        return $result;
    }

    /**
     * Updates the state of the panic alarm.
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function UpdatePanicAlarm(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $variables = json_decode($this->ReadPropertyString('PanicAlarm'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        $state = false;
        foreach ($variables as $variable) {
            if ($variable['Use']) {
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $result = true;
                    $actualValue = GetValueBoolean($variable['ID']);
                    if ($actualValue) {
                        $state = true; //on
                    }
                }
            }
        }
        $this->SetValue('PanicAlarm', $state);
        return $result;
    }
}