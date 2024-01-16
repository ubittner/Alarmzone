<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/helper/
 * @file          AZ_AcknowledgementTone.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

trait AZST_AcknowledgementTone
{
    #################### Public

    /**
     * Executes an acknowledgement tone.
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection mode,
     * 2 =  Hull protection mode,
     * 3 =  Partial protection mode,
     * 4 =  Individual protection mode
     *
     * @return void
     * @throws Exception
     */
    public function ExecuteAcknowledgementTone(int $Mode): void
    {
        $useAction = false;
        switch ($Mode) {
            case 0: //Disarmed
                $useAction = $this->ReadPropertyBoolean('UseAcknowledgementToneDisarmedAction');
                $action = json_decode($this->ReadPropertyString('AcknowledgementToneDisarmedAction'), true);
                break;

            case 1: //Full protection mode
            case 2: //Hull protection mode
            case 3: //Partial protection mode
            case 4: //Individual protection mode
                $useAction = $this->ReadPropertyBoolean('UseAcknowledgementToneArmedAction');
                $action = json_decode($this->ReadPropertyString('AcknowledgementToneArmedAction'), true);
                break;

        }
        if ($useAction && isset($action)) {
            IPS_RunAction($action['actionID'], $action['parameters']);
        }
    }

    #################### Protected

    /**
     * Checks whether the alarm zone controller should use an acknowledgement tone.
     *
     * If the alarm zone! uses an activation check or has a delayed activation,
     * the acknowledgement tone must be done by the alarm zone itself!
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection mode,
     * 2 =  Hull protection mode,
     * 3 =  Partial protection mode,
     * 4 =  Individual protection mode
     *
     * @return bool
     * false = don't use acknowledgement tone
     * true =  use acknowledgement tone
     *
     * @throws Exception
     */
    protected function CheckAlarmZoneControllerAcknowledgementTone(int $Mode): bool
    {
        $useAlarmZoneControllerAcknowledgementTone = false;
        $activationCheck = false;
        $activationDelay = false;
        switch ($Mode) {
            case 0:
                $useAction = 'UseAcknowledgementToneDisarmedAction';
                break;

            case 1: //Full protection mode
                $activationCheck = $this->ReadPropertyBoolean('FullProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('FullProtectionActivationDelay');
                $useAction = 'UseAcknowledgementToneArmedAction';
                break;

            case 2: //Hull protection mode
                $activationCheck = $this->ReadPropertyBoolean('HullProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('HullProtectionActivationDelay');
                $useAction = 'UseAcknowledgementToneArmedAction';
                break;

            case 3: //Partial protection mode
                $activationCheck = $this->ReadPropertyBoolean('PartialProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('PartialProtectionActivationDelay');
                $useAction = 'UseAcknowledgementToneArmedAction';
                break;

            case 4: //Individual protection mode
                $activationCheck = $this->ReadPropertyBoolean('IndividualProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('IndividualProtectionActivationDelay');
                $useAction = 'UseAcknowledgementToneArmedAction';
                break;

        }
        if ($activationCheck || $activationDelay) {
            return false;
        }
        if (isset($useAction)) {
            if ($this->ReadPropertyBoolean($useAction)) {
                $useAlarmZoneControllerAcknowledgementTone = true;
            }
        }
        return $useAlarmZoneControllerAcknowledgementTone;
    }
}