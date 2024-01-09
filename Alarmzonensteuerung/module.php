<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/
 * @file          module.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

declare(strict_types=1);

include_once __DIR__ . '/helper/AZST_autoload.php';

class Alarmzonensteuerung extends IPSModule
{
    //Helper
    use AZST_AlarmProtocol;
    use AZST_ConfigurationForm;
    use AZST_Control;
    use AZST_Notification;
    use AZST_States;

    //Constants
    private const LIBRARY_GUID = '{F227BA9C-8112-3B9F-1149-9B53E10D4F79}';
    private const MODULE_GUID = '{79BB840E-65C1-06E0-E1DD-BAFEFC514848}';
    private const MODULE_PREFIX = 'AZST';
    private const ALARMZONE_MODULE_GUID = '{127AB08D-CD10-801D-D419-442CDE6E5C61}';
    private const ALARMPROTOCOL_MODULE_GUID = '{66BDB59B-E80F-E837-6640-005C32D5FC24}';
    private const NOTIFICATION_MODULE_GUID = '{BDAB70AA-B45D-4CB4-3D65-509CFF0969F9}';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        ########## Properties

        ##### Info

        $this->RegisterPropertyString('Note', '');

        ##### Designations

        $this->RegisterPropertyString('SystemName', 'Alarmanlage');
        $this->RegisterPropertyString('Location', '');

        ##### Operating modes

        //Alarm
        $this->RegisterPropertyBoolean('UseDisarmAlarmZonesWhenAlarmSwitchIsOff', false);
        $this->RegisterPropertyString('AlertingSensorNameWhenAlarmSwitchIsOn', 'Panikalarm');
        $this->RegisterPropertyBoolean('UseAlarmSirenWhenAlarmSwitchIsOn', false);
        $this->RegisterPropertyBoolean('UseAlarmLightWhenAlarmSwitchIsOn', false);
        $this->RegisterPropertyBoolean('UseAlarmCallWhenAlarmSwitchIsOn', false);
        $this->RegisterPropertyBoolean('UsePanicAlarmWhenAlarmSwitchIsOn', false);

        //Disarmed
        $this->RegisterPropertyString('DisarmedIcon', 'Warning');
        $this->RegisterPropertyString('DisarmedName', 'Unscharf');
        $this->RegisterPropertyInteger('DisarmedColor', 65280);

        //Full protection
        $this->RegisterPropertyBoolean('UseFullProtectionMode', true);
        $this->RegisterPropertyString('FullProtectionIcon', 'Basement');
        $this->RegisterPropertyString('FullProtectionName', 'Vollschutz');
        $this->RegisterPropertyInteger('FullProtectionColor', 16711680);

        //Hull protection
        $this->RegisterPropertyBoolean('UseHullProtectionMode', false);
        $this->RegisterPropertyString('HullProtectionIcon', 'Presence');
        $this->RegisterPropertyString('HullProtectionName', 'Hüllschutz');
        $this->RegisterPropertyInteger('HullProtectionColor', 16776960);

        //Partial protection
        $this->RegisterPropertyBoolean('UsePartialProtectionMode', false);
        $this->RegisterPropertyString('PartialProtectionIcon', 'Moon');
        $this->RegisterPropertyString('PartialProtectionName', 'Teilschutz');
        $this->RegisterPropertyInteger('PartialProtectionColor', 255);

        //Individual protection
        $this->RegisterPropertyBoolean('UseIndividualProtectionMode', false);
        $this->RegisterPropertyString('IndividualProtectionIcon', 'Eyes');
        $this->RegisterPropertyString('IndividualProtectionName', 'Individualschutz');
        $this->RegisterPropertyInteger('IndividualProtectionColor', 16749824);

        ##### Alarm zones

        $this->RegisterPropertyString('AlarmZones', '[]');
        $this->RegisterPropertyString('ProtectionMode', '[]');
        $this->RegisterPropertyString('GlassBreakageDetectorControl', '[]');
        $this->RegisterPropertyString('SystemState', '[]');
        $this->RegisterPropertyString('SystemDetailedState', '[]');
        $this->RegisterPropertyString('AlarmState', '[]');
        $this->RegisterPropertyString('AlertingSensor', '[]');
        $this->RegisterPropertyString('DoorWindowState', '[]');
        $this->RegisterPropertyString('MotionDetectorState', '[]');
        $this->RegisterPropertyString('GlassBreakageDetectorState', '[]');
        $this->RegisterPropertyString('SmokeDetectorState', '[]');
        $this->RegisterPropertyString('WaterDetectorState', '[]');
        $this->RegisterPropertyString('AlarmSiren', '[]');
        $this->RegisterPropertyString('AlarmLight', '[]');
        $this->RegisterPropertyString('AlarmCall', '[]');
        $this->RegisterPropertyString('PanicAlarm', '[]');

        ##### Alarm protocol

        $this->RegisterPropertyInteger('AlarmProtocol', 0);

        ##### Notification

        //Notification center
        $this->RegisterPropertyInteger('Notification', 0);
        //Notification alarm
        $this->RegisterPropertyString('PanicAlarmNotification', '[{"Use":false,"Designation":"Panikalarm","SpacerNotification":"","LabelMessageText":"","MessageText":"⚠️%1$s wurde ausgelöst!","UseTimestamp":true,"SpacerWebFrontNotification":"","LabelWebFrontNotification":"","UseWebFrontNotification":false,"WebFrontNotificationTitle":"","WebFrontNotificationIcon":"","WebFrontNotificationDisplayDuration":0,"SpacerWebFrontPushNotification":"","LabelWebFrontPushNotification":"","UseWebFrontPushNotification":false,"WebFrontPushNotificationTitle":"","WebFrontPushNotificationSound":"alarm","WebFrontPushNotificationTargetID":0,"SpacerMail":"","LabelMail":"","UseMailer":false,"Subject":"","SpacerSMS":"","LabelSMS":"","UseSMS":false,"SMSTitle":"","SpacerTelegram":"","LabelTelegram":"","UseTelegram":false,"TelegramTitle":""}]');

        ###### Actions

        $parameters = '{"actionID":"{346AA8C1-30E0-1663-78EF-93EFADFAC650}","parameters":{"SCRIPT":"<?php\n\n//Skript hier einfügen","ENVIRONMENT":"Default","PARENT":' . $this->InstanceID . ',"TARGET":' . $this->InstanceID . '}}';
        $this->RegisterPropertyBoolean('UseDisarmedAction', false);
        $this->RegisterPropertyString('DisarmedAction', $parameters);
        $this->RegisterPropertyBoolean('UseFullProtectionAction', false);
        $this->RegisterPropertyString('FullProtectionAction', $parameters);
        $this->RegisterPropertyBoolean('UseHullProtectionAction', false);
        $this->RegisterPropertyString('HullProtectionAction', $parameters);
        $this->RegisterPropertyBoolean('UsePartialProtectionAction', false);
        $this->RegisterPropertyString('PartialProtectionAction', $parameters);
        $this->RegisterPropertyBoolean('UseIndividualProtectionAction', false);
        $this->RegisterPropertyString('IndividualProtectionAction', $parameters);

        ##### Visualisation

        $this->RegisterPropertyBoolean('EnableActive', false);
        $this->RegisterPropertyBoolean('EnableLocation', true);
        $this->RegisterPropertyBoolean('EnableAlarmSwitch', true);
        $this->RegisterPropertyBoolean('EnableAlertingSensor', true);
        $this->RegisterPropertyBoolean('EnableFullProtectionControlSwitch', true);
        $this->RegisterPropertyBoolean('EnableHullProtectionControlSwitch', true);
        $this->RegisterPropertyBoolean('EnablePartialProtectionControlSwitch', true);
        $this->RegisterPropertyBoolean('EnableIndividualProtectionControlSwitch', true);
        $this->RegisterPropertyBoolean('EnableGlassBreakageDetectorControlSwitch', false);
        $this->RegisterPropertyBoolean('EnableMode', true);
        $this->RegisterPropertyBoolean('EnableSystemState', true);
        $this->RegisterPropertyBoolean('EnableSystemDetailedState', false);
        $this->RegisterPropertyBoolean('EnableDoorWindowState', true);
        $this->RegisterPropertyBoolean('EnableMotionDetectorState', false);
        $this->RegisterPropertyBoolean('EnableGlassBreakageDetectorState', false);
        $this->RegisterPropertyBoolean('EnableSmokeDetectorState', false);
        $this->RegisterPropertyBoolean('EnableWaterDetectorState', false);
        $this->RegisterPropertyBoolean('EnableAlarmState', true);
        $this->RegisterPropertyBoolean('EnableAlarmSirenState', false);
        $this->RegisterPropertyBoolean('EnableAlarmLightState', false);
        $this->RegisterPropertyBoolean('EnableAlarmCallState', false);
        $this->RegisterPropertyBoolean('EnablePanicAlarmState', false);

        ########## Variables

        //Active
        $id = @$this->GetIDForIdent('Active');
        $this->RegisterVariableBoolean('Active', 'Aktiv', '~Switch', 10);
        $this->EnableAction('Active');
        if (!$id) {
            $this->SetValue('Active', true);
        }

        //Location
        $id = @$this->GetIDForIdent('Location');
        $this->RegisterVariableString('Location', 'Standortbezeichnung', '', 20);
        $this->SetValue('Location', $this->ReadPropertyString('Location'));
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('Location'), 'IPS');
        }

        //Alarm switch
        $id = @$this->GetIDForIdent('AlarmSwitch');
        $this->RegisterVariableBoolean('AlarmSwitch', 'Alarm', '~Switch', 30);
        $this->EnableAction('AlarmSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('AlarmSwitch'), 'Warning');
        }

        //Alerting sensor
        $id = @$this->GetIDForIdent('AlertingSensor');
        $this->RegisterVariableString('AlertingSensor', 'Auslösender Alarmmelder', '', 40);
        $this->SetValue('AlertingSensor', $this->ReadPropertyString('AlertingSensor'));
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('AlertingSensor'), 'Eyes');
        }

        //Full protection control switch
        $id = @$this->GetIDForIdent('FullProtectionControlSwitch');
        $name = $this->ReadPropertyString('FullProtectionName');
        $this->RegisterVariableBoolean('FullProtectionControlSwitch', $name, '~Switch', 50);
        $this->EnableAction('FullProtectionControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('FullProtectionControlSwitch'), 'Basement');
        }

        //Hull protection control switch
        $id = @$this->GetIDForIdent('HullProtectionControlSwitch');
        $name = $this->ReadPropertyString('HullProtectionName');
        $this->RegisterVariableBoolean('HullProtectionControlSwitch', $name, '~Switch', 60);
        $this->EnableAction('HullProtectionControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('HullProtectionControlSwitch'), 'GroundFloor');
        }

        //Partial protection control switch
        $id = @$this->GetIDForIdent('PartialProtectionControlSwitch');
        $name = $this->ReadPropertyString('PartialProtectionName');
        $this->RegisterVariableBoolean('PartialProtectionControlSwitch', $name, '~Switch', 70);
        $this->EnableAction('PartialProtectionControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('PartialProtectionControlSwitch'), 'Moon');
        }

        //Individual protection control switch
        $id = @$this->GetIDForIdent('IndividualProtectionControlSwitch');
        $name = $this->ReadPropertyString('IndividualProtectionName');
        $this->RegisterVariableBoolean('IndividualProtectionControlSwitch', $name, '~Switch', 80);
        $this->EnableAction('IndividualProtectionControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('IndividualProtectionControlSwitch'), 'IPS');
        }

        //Glass breakage detector control switch
        $id = @$this->GetIDForIdent('GlassBreakageDetectorControlSwitch');
        $this->RegisterVariableBoolean('GlassBreakageDetectorControlSwitch', 'Glasbruchmelder', '~Switch', 90);
        $this->EnableAction('GlassBreakageDetectorControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('GlassBreakageDetectorControlSwitch'), 'Window');
        }

        //System state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.SystemState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, 'Unscharf', 'IPS', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Scharf', 'Warning', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 2, 'Verzögert Scharf', 'Clock', 0xFFFF00);
        $this->RegisterVariableInteger('SystemState', 'Systemstatus', $profile, 110);

        //System detailed state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.SystemDetailedState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, 'Unscharf', 'IPS', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Scharf', 'Warning', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 2, 'Verzögert Scharf', 'Clock', 0xFFFF00);
        IPS_SetVariableProfileAssociation($profile, 3, 'Teilscharf', 'Warning', 0xFFFF00);
        IPS_SetVariableProfileAssociation($profile, 4, 'Verzögert Teilscharf', 'Warning', 0xFFFF00);
        $this->RegisterVariableInteger('SystemDetailedState', 'Detaillierter Systemstatus', $profile, 120);

        //Door and window state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.DoorWindowState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Window');
        IPS_SetVariableProfileAssociation($profile, 0, 'Geschlossen', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Geöffnet', '', 0xFF0000);
        $this->RegisterVariableBoolean('DoorWindowState', 'Tür und Fensterstatus', $profile, 130);

        //Motion detector state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.MotionDetectorState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Motion');
        IPS_SetVariableProfileAssociation($profile, 0, 'OK', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Bewegung erkannt', '', 0xFF0000);
        $this->RegisterVariableBoolean('MotionDetectorState', 'Bewegungsmelderstatus', $profile, 140);

        //Glass breakage detector state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.GlassBreakageDetectorState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Window');
        IPS_SetVariableProfileAssociation($profile, 0, 'OK', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Glasbruch erkannt', '', 0xFF0000);
        $this->RegisterVariableBoolean('GlassBreakageDetectorState', 'Glasbruchmelderstatus', $profile, 150);

        //Smoke detector state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.SmokeDetectorState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Flame');
        IPS_SetVariableProfileAssociation($profile, 0, 'OK', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Rauch erkannt', '', 0xFF0000);
        $this->RegisterVariableBoolean('SmokeDetectorState', 'Rauchhmelderstatus', $profile, 160);

        //Water detector state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.WaterDetectorState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Tap');
        IPS_SetVariableProfileAssociation($profile, 0, 'OK', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Wasser erkannt', '', 0xFF0000);
        $this->RegisterVariableBoolean('WaterDetectorState', 'Wassermelderstatus', $profile, 170);

        //Alarm state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, 'OK', 'Warning', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Alarm', 'Alert', 0xFF0000);
        $this->RegisterVariableInteger('AlarmState', 'Alarmstatus', $profile, 180);

        //Alarm siren status
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmSirenStatus';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Alert');
        IPS_SetVariableProfileAssociation($profile, 0, 'Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'An', '', 0xFF0000);
        $this->RegisterVariableBoolean('AlarmSiren', 'Alarmsirene', $profile, 190);

        //Alarm light status
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmLightStatus';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Bulb');
        IPS_SetVariableProfileAssociation($profile, 0, 'Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'An', '', 0xFF0000);
        $this->RegisterVariableBoolean('AlarmLight', 'Alarmbeleuchtung', $profile, 200);

        //Alarm call status
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmCallStatus';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Mobile');
        IPS_SetVariableProfileAssociation($profile, 0, 'Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'An', '', 0xFF0000);
        $this->RegisterVariableBoolean('AlarmCall', 'Alarmanruf', $profile, 210);

        //Panic alarm status
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.PanicAlarmStatus';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Warning');
        IPS_SetVariableProfileAssociation($profile, 0, 'Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'An', '', 0xFF0000);
        $this->RegisterVariableBoolean('PanicAlarm', 'Panikalarm', $profile, 220);

        ########## Attribute

        $this->RegisterAttributeBoolean('DisableUpdateMode', false);
        $this->RegisterAttributeBoolean('DisableUpdateGlassBreakageDetectorControl', false);
    }

    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        //Never delete this line!
        parent::ApplyChanges();

        // Check runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        ########## Maintain variable

        //Mode
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.Mode';
        if (IPS_VariableProfileExists($profile)) {
            IPS_DeleteVariableProfile($profile);
        }
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 0, $this->ReadPropertyString('DisarmedName'), $this->ReadPropertyString('DisarmedIcon'), $this->ReadPropertyInteger('DisarmedColor'));
        if ($this->ReadPropertyBoolean('UseFullProtectionMode')) {
            IPS_SetVariableProfileAssociation($profile, 1, $this->ReadPropertyString('FullProtectionName'), $this->ReadPropertyString('FullProtectionIcon'), $this->ReadPropertyInteger('FullProtectionColor'));
        }
        if ($this->ReadPropertyBoolean('UseHullProtectionMode')) {
            IPS_SetVariableProfileAssociation($profile, 2, $this->ReadPropertyString('HullProtectionName'), $this->ReadPropertyString('HullProtectionIcon'), $this->ReadPropertyInteger('HullProtectionColor'));
        }
        if ($this->ReadPropertyBoolean('UsePartialProtectionMode')) {
            IPS_SetVariableProfileAssociation($profile, 3, $this->ReadPropertyString('PartialProtectionName'), $this->ReadPropertyString('PartialProtectionIcon'), $this->ReadPropertyInteger('PartialProtectionColor'));
        }
        if ($this->ReadPropertyBoolean('UseIndividualProtectionMode')) {
            IPS_SetVariableProfileAssociation($profile, 4, $this->ReadPropertyString('IndividualProtectionName'), $this->ReadPropertyString('IndividualProtectionIcon'), $this->ReadPropertyInteger('IndividualProtectionColor'));
        }
        $this->RegisterVariableInteger('Mode', 'Modus', $profile, 100);
        $this->MaintainVariable('Mode', 'Modus', 1, $profile, 100, true);
        $this->EnableAction('Mode');

        ########## Options

        //Active
        IPS_SetHidden($this->GetIDForIdent('Active'), !$this->ReadPropertyBoolean('EnableActive'));

        //Location
        $this->SetValue('Location', $this->ReadPropertyString('Location'));
        IPS_SetHidden($this->GetIDForIdent('Location'), !$this->ReadPropertyBoolean('EnableLocation'));

        //Alarm switch
        IPS_SetHidden($this->GetIDForIdent('AlarmSwitch'), !$this->ReadPropertyBoolean('EnableAlarmSwitch'));

        //Alerting sensor
        IPS_SetHidden($this->GetIDForIdent('AlertingSensor'), !$this->ReadPropertyBoolean('EnableAlertingSensor'));

        //Control switches
        IPS_SetHidden($this->GetIDForIdent('FullProtectionControlSwitch'), !$this->ReadPropertyBoolean('EnableFullProtectionControlSwitch'));
        IPS_SetHidden($this->GetIDForIdent('HullProtectionControlSwitch'), !$this->ReadPropertyBoolean('EnableHullProtectionControlSwitch'));
        IPS_SetHidden($this->GetIDForIdent('PartialProtectionControlSwitch'), !$this->ReadPropertyBoolean('EnablePartialProtectionControlSwitch'));
        IPS_SetHidden($this->GetIDForIdent('IndividualProtectionControlSwitch'), !$this->ReadPropertyBoolean('EnableIndividualProtectionControlSwitch'));

        //Mode
        IPS_SetHidden($this->GetIDForIdent('Mode'), !$this->ReadPropertyBoolean('EnableMode'));

        //Glass breakage detector control switch
        IPS_SetHidden($this->GetIDForIdent('GlassBreakageDetectorControlSwitch'), !$this->ReadPropertyBoolean('EnableGlassBreakageDetectorControlSwitch'));

        //System state
        IPS_SetHidden($this->GetIDForIdent('SystemState'), !$this->ReadPropertyBoolean('EnableSystemState'));

        //System detailed state
        IPS_SetHidden($this->GetIDForIdent('SystemDetailedState'), !$this->ReadPropertyBoolean('EnableSystemDetailedState'));

        //Door and window state
        IPS_SetHidden($this->GetIDForIdent('DoorWindowState'), !$this->ReadPropertyBoolean('EnableDoorWindowState'));

        //Motion detector state
        IPS_SetHidden($this->GetIDForIdent('MotionDetectorState'), !$this->ReadPropertyBoolean('EnableMotionDetectorState'));

        //Glass breakage detector state
        IPS_SetHidden($this->GetIDForIdent('GlassBreakageDetectorState'), !$this->ReadPropertyBoolean('EnableGlassBreakageDetectorState'));

        //Smoke detector state
        IPS_SetHidden($this->GetIDForIdent('SmokeDetectorState'), !$this->ReadPropertyBoolean('EnableSmokeDetectorState'));

        //Water detector state
        IPS_SetHidden($this->GetIDForIdent('WaterDetectorState'), !$this->ReadPropertyBoolean('EnableWaterDetectorState'));

        //Alarm state
        IPS_SetHidden($this->GetIDForIdent('AlarmState'), !$this->ReadPropertyBoolean('EnableAlarmState'));

        //Alarm siren state
        IPS_SetHidden($this->GetIDForIdent('AlarmSiren'), !$this->ReadPropertyBoolean('EnableAlarmSirenState'));

        //Alarm light state
        IPS_SetHidden($this->GetIDForIdent('AlarmLight'), !$this->ReadPropertyBoolean('EnableAlarmLightState'));

        //Alarm call state
        IPS_SetHidden($this->GetIDForIdent('AlarmCall'), !$this->ReadPropertyBoolean('EnableAlarmCallState'));

        //Panic alarm state
        IPS_SetHidden($this->GetIDForIdent('PanicAlarm'), !$this->ReadPropertyBoolean('EnablePanicAlarmState'));

        ########## Attribute

        $this->WriteAttributeBoolean('DisableUpdateMode', false);
        $this->WriteAttributeBoolean('DisableUpdateGlassBreakageDetectorControl', false);

        //Delete all references
        foreach ($this->GetReferenceList() as $referenceID) {
            $this->UnregisterReference($referenceID);
        }

        //Delete all update messages
        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $message) {
                if ($message == VM_UPDATE) {
                    $this->UnregisterMessage($senderID, VM_UPDATE);
                }
            }
        }

        //Register references and update messages
        $alarmZones = json_decode($this->ReadPropertyString('AlarmZones'), true);
        foreach ($alarmZones as $alarmZone) {
            if (!$alarmZone['Use']) {
                continue;
            }
            $id = $alarmZone['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $this->RegisterReference($id);
            }
        }
        $properties = [
            'ProtectionMode',
            'GlassBreakageDetectorControl',
            'SystemState',
            'SystemDetailedState',
            'AlarmState',
            'AlertingSensor',
            'DoorWindowState',
            'MotionDetectorState',
            'GlassBreakageDetectorState',
            'SmokeDetectorState',
            'WaterDetectorState',
            'AlarmSiren',
            'AlarmLight',
            'AlarmCall',
            'PanicAlarm'];
        foreach ($properties as $property) {
            $variables = json_decode($this->ReadPropertyString($property), true);
            foreach ($variables as $variable) {
                if ($variable['Use']) {
                    $id = $variable['ID'];
                    if ($id > 1 && IPS_ObjectExists($id)) {
                        $this->RegisterReference($id);
                        $this->RegisterMessage($id, VM_UPDATE);
                    }
                }
            }
        }

        //Notification
        $id = $this->ReadPropertyInteger('Notification');
        if ($id > 1 && @IPS_ObjectExists($id)) {
            $this->RegisterReference($id);
        }

        $this->UpdateStates();
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();

        //Delete profiles
        $profiles = [
            'Mode',
            'SystemState',
            'SystemDetailedState',
            'AlarmState', 'DoorWindowState',
            'MotionDetectorState',
            'GlassBreakageDetectorState',
            'SmokeDetectorState',
            'WaterDetectorState',
            'AlarmSirenStatus',
            'AlarmLightStatus',
            'AlarmCallStatus',
            'PanicAlarmStatus'];
        if (!empty($profiles)) {
            foreach ($profiles as $profile) {
                $profileName = self::MODULE_PREFIX . '.' . $this->InstanceID . '.' . $profile;
                $this->UnregisterProfile($profileName);
            }
        }
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug('MessageSink', 'Message from SenderID ' . $SenderID . ' with Message ' . $Message . "\r\n Data: " . print_r($Data, true), 0);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

            case VM_UPDATE:

                //$Data[0] = actual value
                //$Data[1] = value changed
                //$Data[2] = last value
                //$Data[3] = timestamp actual value
                //$Data[4] = timestamp value changed
                //$Data[5] = timestamp last value

                if ($this->CheckMaintenance()) {
                    return;
                }

                //Check trigger variable
                $properties = [
                    'ProtectionMode',
                    'GlassBreakageDetectorControl',
                    'SystemState',
                    'SystemDetailedState',
                    'AlarmState',
                    'AlertingSensor',
                    'DoorWindowState',
                    'MotionDetectorState',
                    'GlassBreakageDetectorState',
                    'SmokeDetectorState',
                    'WaterDetectorState',
                    'AlarmSiren',
                    'AlarmLight',
                    'AlarmCall',
                    'PanicAlarm'];
                foreach ($properties as $property) {
                    $variables = json_decode($this->ReadPropertyString($property), true);
                    if (!empty($variables)) {
                        if (in_array($SenderID, array_column($variables, 'ID'))) {
                            $scriptText = self::MODULE_PREFIX . '_Update' . $property . '(' . $this->InstanceID . ');';
                            $this->SendDebug(__FUNCTION__, 'Methode: ' . $scriptText, 0);
                            @IPS_RunScriptText($scriptText);
                        }
                    }
                }
                break;

        }
    }

    /**
     * Creates a new alarm zone instance.
     *
     * @return void
     */
    public function CreateAlarmZoneInstance(): void
    {
        $id = @IPS_CreateInstance(self::ALARMZONE_MODULE_GUID);
        if (is_int($id)) {
            IPS_SetName($id, 'Alarmzone');
            $infoText = 'Eine neue Alarmzone mit der ID ' . $id . ' wurde erfolgreich erstellt!';
        } else {
            $infoText = 'Alarmzone konnte nicht erstellt werden!';
        }
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
    }

    /**
     * Creates a new alarm protocol instance.
     *
     * @return void
     */
    public function CreateAlarmProtocolInstance(): void
    {
        $id = @IPS_CreateInstance(self::ALARMPROTOCOL_MODULE_GUID);
        if (is_int($id)) {
            IPS_SetName($id, 'Alarmprotokoll');
            $infoText = 'Instanz mit der ID ' . $id . ' wurde erfolgreich erstellt!';
        } else {
            $infoText = 'Instanz konnte nicht erstellt werden!';
        }
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
    }

    /**
     * Creates a new notification instance.
     *
     * @return void
     */
    public function CreateNotificationInstance(): void
    {
        $id = IPS_CreateInstance(self::NOTIFICATION_MODULE_GUID);
        if (is_int($id)) {
            IPS_SetName($id, 'Benachrichtigung');
            $infoText = 'Instanz mit der ID ' . $id . ' wurde erfolgreich erstellt!';
        } else {
            $infoText = 'Instanz konnte nicht erstellt werden!';
        }
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
    }

    public function UIShowMessage(string $Message): void
    {
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $Message);
    }

    public function ReorderVariables(): void
    {
        IPS_SetPosition($this->GetIDForIdent('Active'), 10);
        IPS_SetPosition($this->GetIDForIdent('Location'), 20);
        IPS_SetPosition($this->GetIDForIdent('AlarmSwitch'), 30);
        IPS_SetPosition($this->GetIDForIdent('AlertingSensor'), 40);
        IPS_SetPosition($this->GetIDForIdent('FullProtectionControlSwitch'), 50);
        IPS_SetPosition($this->GetIDForIdent('HullProtectionControlSwitch'), 60);
        IPS_SetPosition($this->GetIDForIdent('PartialProtectionControlSwitch'), 70);
        IPS_SetPosition($this->GetIDForIdent('IndividualProtectionControlSwitch'), 80);
        IPS_SetPosition($this->GetIDForIdent('GlassBreakageDetectorControlSwitch'), 90);
        IPS_SetPosition($this->GetIDForIdent('Mode'), 100);
        IPS_SetPosition($this->GetIDForIdent('SystemState'), 110);
        IPS_SetPosition($this->GetIDForIdent('SystemDetailedState'), 120);
        IPS_SetPosition($this->GetIDForIdent('DoorWindowState'), 130);
        IPS_SetPosition($this->GetIDForIdent('MotionDetectorState'), 140);
        IPS_SetPosition($this->GetIDForIdent('GlassBreakageDetectorState'), 150);
        IPS_SetPosition($this->GetIDForIdent('SmokeDetectorState'), 160);
        IPS_SetPosition($this->GetIDForIdent('WaterDetectorState'), 170);
        IPS_SetPosition($this->GetIDForIdent('AlarmState'), 180);
        IPS_SetPosition($this->GetIDForIdent('AlarmSiren'), 190);
        IPS_SetPosition($this->GetIDForIdent('AlarmLight'), 200);
        IPS_SetPosition($this->GetIDForIdent('AlarmCall'), 210);
        IPS_SetPosition($this->GetIDForIdent('PanicAlarm'), 220);
    }

    #################### Request Action

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {

            case 'Active':
                $this->SetValue($Ident, $Value);
                break;

            case 'AlarmSwitch':
                $this->SetAlarm($Value);
                break;

            case 'FullProtectionControlSwitch':
                $mode = 0; //disarm
                if ($Value) {
                    $mode = 1; //full protection
                }
                $id = $this->GetIDForIdent('FullProtectionControlSwitch');
                $this->SelectProtectionMode($mode, (string) $id);
                break;

            case 'HullProtectionControlSwitch':
                $mode = 0; //disarm
                if ($Value) {
                    $mode = 2; //hull protection
                }
                $id = $this->GetIDForIdent('HullProtectionControlSwitch');
                $this->SelectProtectionMode($mode, (string) $id);
                break;

            case 'PartialProtectionControlSwitch':
                $mode = 0; //disarm
                if ($Value) {
                    $mode = 3; //partial protection
                }
                $id = $this->GetIDForIdent('PartialProtectionControlSwitch');
                $this->SelectProtectionMode($mode, (string) $id);
                break;

            case 'IndividualProtectionControlSwitch':
                $mode = 0; //disarm
                if ($Value) {
                    $mode = 4; //partial protection
                }
                $id = $this->GetIDForIdent('IndividualProtectionControlSwitch');
                $this->SelectProtectionMode($mode, (string) $id);
                break;

            case 'Mode':
                $id = $this->GetIDForIdent('Mode');
                $this->SelectProtectionMode($Value, (string) $id);
                break;

            case 'GlassBreakageDetectorControlSwitch':
                $this->SwitchGlassBreakageDetectorControl($Value);
                break;

        }
    }

    #################### Private

    private function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    /**
     * Unregisters a variable profile.
     *
     * @param string $Name
     * @return void
     */
    private function UnregisterProfile(string $Name): void
    {
        if (!IPS_VariableProfileExists($Name)) {
            return;
        }
        foreach (IPS_GetVariableList() as $VarID) {
            if (IPS_GetParent($VarID) == $this->InstanceID) {
                continue;
            }
            if (IPS_GetVariable($VarID)['VariableCustomProfile'] == $Name) {
                return;
            }
            if (IPS_GetVariable($VarID)['VariableProfile'] == $Name) {
                return;
            }
        }
        foreach (IPS_GetMediaListByType(MEDIATYPE_CHART) as $mediaID) {
            $content = json_decode(base64_decode(IPS_GetMediaContent($mediaID)), true);
            foreach ($content['axes'] as $axis) {
                if ($axis['profile' === $Name]) {
                    return;
                }
            }
        }
        IPS_DeleteVariableProfile($Name);
    }

    /**
     * Checks for maintenance.
     *
     * @return bool
     * false =  active,
     * true =   inactive
     */
    private function CheckMaintenance(): bool
    {
        $result = false;
        if (!$this->GetValue('Active')) {
            $this->SendDebug(__FUNCTION__, 'Abbruch, die Instanz ist inaktiv!', 0);
            $result = true;
        }
        return $result;
    }
}