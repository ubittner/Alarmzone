<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung
 * @file          module.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

declare(strict_types=1);

include_once __DIR__ . '/helper/AZST_autoload.php';

class Alarmzonensteuerung extends IPSModule
{
    //Helper
    use AZST_Config;
    use AZST_Control;
    use AZST_States;

    //Constants
    private const LIBRARY_GUID = '{F227BA9C-8112-3B9F-1149-9B53E10D4F79}';
    private const MODULE_GUID = '{79BB840E-65C1-06E0-E1DD-BAFEFC514848}';
    private const MODULE_NAME = 'Alarmzonensteuerung';
    private const MODULE_PREFIX = 'AZST';
    private const ALARMZONE_MODULE_GUID = '{127AB08D-CD10-801D-D419-442CDE6E5C61}';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        ########## Properties

        ##### Info

        $this->RegisterPropertyString('Note', '');

        ##### Designations

        $this->RegisterPropertyString('Location', '');

        ##### Operating modes

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
        $this->RegisterPropertyString('SystemState', '[]');
        $this->RegisterPropertyString('SystemDetailedState', '[]');
        $this->RegisterPropertyString('AlarmState', '[]');
        $this->RegisterPropertyString('AlertingSensor', '[]');
        $this->RegisterPropertyString('DoorWindowState', '[]');
        $this->RegisterPropertyString('MotionDetectorState', '[]');
        $this->RegisterPropertyString('AlarmSiren', '[]');
        $this->RegisterPropertyString('AlarmLight', '[]');
        $this->RegisterPropertyString('AlarmCall', '[]');

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
        $this->RegisterPropertyBoolean('EnableFullProtectionControlSwitch', true);
        $this->RegisterPropertyBoolean('EnableHullProtectionControlSwitch', true);
        $this->RegisterPropertyBoolean('EnablePartialProtectionControlSwitch', true);
        $this->RegisterPropertyBoolean('EnableIndividualProtectionControlSwitch', true);
        $this->RegisterPropertyBoolean('EnableMode', true);
        $this->RegisterPropertyBoolean('EnableSystemState', true);
        $this->RegisterPropertyBoolean('EnableSystemDetailedState', false);
        $this->RegisterPropertyBoolean('EnableDoorWindowState', true);
        $this->RegisterPropertyBoolean('EnableMotionDetectorState', true);
        $this->RegisterPropertyBoolean('EnableAlarmState', true);
        $this->RegisterPropertyBoolean('EnableAlertingSensor', true);
        $this->RegisterPropertyBoolean('EnableAlarmSirenState', false);
        $this->RegisterPropertyBoolean('EnableAlarmLightState', false);
        $this->RegisterPropertyBoolean('EnableAlarmCallState', false);

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

        //Full protection control switch
        $id = @$this->GetIDForIdent('FullProtectionControlSwitch');
        $name = $this->ReadPropertyString('FullProtectionName');
        $this->RegisterVariableBoolean('FullProtectionControlSwitch', $name, '~Switch', 26);
        $this->EnableAction('FullProtectionControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('FullProtectionControlSwitch'), 'Basement');
        }

        //Hull protection control switch
        $id = @$this->GetIDForIdent('HullProtectionControlSwitch');
        $name = $this->ReadPropertyString('HullProtectionName');
        $this->RegisterVariableBoolean('HullProtectionControlSwitch', $name, '~Switch', 27);
        $this->EnableAction('HullProtectionControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('HullProtectionControlSwitch'), 'GroundFloor');
        }

        //Partial protection control switch
        $id = @$this->GetIDForIdent('PartialProtectionControlSwitch');
        $name = $this->ReadPropertyString('PartialProtectionName');
        $this->RegisterVariableBoolean('PartialProtectionControlSwitch', $name, '~Switch', 28);
        $this->EnableAction('PartialProtectionControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('PartialProtectionControlSwitch'), 'Moon');
        }

        //Individual protection control switch
        $id = @$this->GetIDForIdent('IndividualProtectionControlSwitch');
        $name = $this->ReadPropertyString('IndividualProtectionName');
        $this->RegisterVariableBoolean('IndividualProtectionControlSwitch', $name, '~Switch', 29);
        $this->EnableAction('IndividualProtectionControlSwitch');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('IndividualProtectionControlSwitch'), 'IPS');
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
        $this->RegisterVariableInteger('SystemState', 'Systemstatus', $profile, 40);

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
        $this->RegisterVariableInteger('SystemDetailedState', 'Detaillierter Systemstatus', $profile, 50);

        //Door and window state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.DoorWindowState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Window');
        IPS_SetVariableProfileAssociation($profile, 0, 'Geschlossen', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Geöffnet', '', 0xFF0000);
        $this->RegisterVariableBoolean('DoorWindowState', 'Tür und Fensterstatus', $profile, 60);

        //Motion detector state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.MotionDetectorState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Motion');
        IPS_SetVariableProfileAssociation($profile, 0, 'OK', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Bewegung erkannt', '', 0xFF0000);
        $this->RegisterVariableBoolean('MotionDetectorState', 'Bewegungsmelderstatus', $profile, 70);

        //Alarm state
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, 'OK', 'Warning', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Alarm', 'Alert', 0xFF0000);
        $this->RegisterVariableInteger('AlarmState', 'Alarmstatus', $profile, 80);

        //Alerting sensor
        $id = @$this->GetIDForIdent('AlertingSensor');
        $this->RegisterVariableString('AlertingSensor', 'Auslösender Alarmsensor', '', 90);
        $this->SetValue('AlertingSensor', $this->ReadPropertyString('AlertingSensor'));
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('AlertingSensor'), 'Eyes');
        }

        //Alarm siren
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmSirenStatus';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Alert');
        IPS_SetVariableProfileAssociation($profile, 0, 'Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'An', '', 0xFF0000);
        $this->RegisterVariableBoolean('AlarmSiren', 'Alarmsirene', $profile, 100);

        //Alarm light
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmLightStatus';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Bulb');
        IPS_SetVariableProfileAssociation($profile, 0, 'Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'An', '', 0xFF0000);
        $this->RegisterVariableBoolean('AlarmLight', 'Alarmbeleuchtung', $profile, 110);

        //Alarm call
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmCallStatus';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Mobile');
        IPS_SetVariableProfileAssociation($profile, 0, 'Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'An', '', 0xFF0000);
        $this->RegisterVariableBoolean('AlarmCall', 'Alarmanruf', $profile, 120);

        ########## Attribute

        $this->RegisterAttributeBoolean('DisableUpdateMode', false);
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
        $this->RegisterVariableInteger('Mode', 'Modus', $profile, 30);
        $this->MaintainVariable('Mode', 'Modus', 1, $profile, 30, true);
        $this->EnableAction('Mode');

        ########## Options

        //Active
        IPS_SetHidden($this->GetIDForIdent('Active'), !$this->ReadPropertyBoolean('EnableActive'));

        //Location
        $this->SetValue('Location', $this->ReadPropertyString('Location'));
        IPS_SetHidden($this->GetIDForIdent('Location'), !$this->ReadPropertyBoolean('EnableLocation'));

        //Control switches
        IPS_SetHidden($this->GetIDForIdent('FullProtectionControlSwitch'), !$this->ReadPropertyBoolean('EnableFullProtectionControlSwitch'));
        IPS_SetHidden($this->GetIDForIdent('HullProtectionControlSwitch'), !$this->ReadPropertyBoolean('EnableHullProtectionControlSwitch'));
        IPS_SetHidden($this->GetIDForIdent('PartialProtectionControlSwitch'), !$this->ReadPropertyBoolean('EnablePartialProtectionControlSwitch'));
        IPS_SetHidden($this->GetIDForIdent('IndividualProtectionControlSwitch'), !$this->ReadPropertyBoolean('EnableIndividualProtectionControlSwitch'));

        //Mode
        IPS_SetHidden($this->GetIDForIdent('Mode'), !$this->ReadPropertyBoolean('EnableMode'));

        //System state
        IPS_SetHidden($this->GetIDForIdent('SystemState'), !$this->ReadPropertyBoolean('EnableSystemState'));

        //System detailed state
        IPS_SetHidden($this->GetIDForIdent('SystemDetailedState'), !$this->ReadPropertyBoolean('EnableSystemDetailedState'));

        //Door and window state
        IPS_SetHidden($this->GetIDForIdent('DoorWindowState'), !$this->ReadPropertyBoolean('EnableDoorWindowState'));

        //Motion detector state
        IPS_SetHidden($this->GetIDForIdent('MotionDetectorState'), !$this->ReadPropertyBoolean('EnableMotionDetectorState'));

        //Alarm state
        IPS_SetHidden($this->GetIDForIdent('AlarmState'), !$this->ReadPropertyBoolean('EnableAlarmState'));

        //Alarm siren state
        IPS_SetHidden($this->GetIDForIdent('AlarmSiren'), !$this->ReadPropertyBoolean('EnableAlarmSirenState'));

        //Alarm light state
        IPS_SetHidden($this->GetIDForIdent('AlarmLight'), !$this->ReadPropertyBoolean('EnableAlarmLightState'));

        //Alarm call state
        IPS_SetHidden($this->GetIDForIdent('AlarmCall'), !$this->ReadPropertyBoolean('EnableAlarmCallState'));

        //Alerting sensor
        IPS_SetHidden($this->GetIDForIdent('AlertingSensor'), !$this->ReadPropertyBoolean('EnableAlertingSensor'));

        ########## Attribute

        $this->WriteAttributeBoolean('DisableUpdateMode', false);

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
            'SystemState',
            'SystemDetailedState',
            'AlarmState',
            'AlertingSensor',
            'DoorWindowState',
            'MotionDetectorState',
            'AlarmSiren',
            'AlarmLight',
            'AlarmCall'];
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
        $this->UpdateStates();
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();

        //Delete profiles
        $profiles = ['Mode', 'SystemState', 'SystemDetailedState', 'AlarmState', 'DoorWindowState', 'MotionDetectorState', 'AlarmSirenStatus', 'AlarmLightStatus', 'AlarmCallStatus'];
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
                    'SystemState',
                    'SystemDetailedState',
                    'AlarmState',
                    'AlertingSensor',
                    'DoorWindowState',
                    'MotionDetectorState',
                    'AlarmSiren',
                    'AlarmLight',
                    'AlarmCall'];
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

    #################### Request Action

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {

            case 'Active':
                $this->SetValue($Ident, $Value);
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