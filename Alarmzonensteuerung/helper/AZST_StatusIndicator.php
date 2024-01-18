<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/helper/
 * @file          AZ_StatusIndicator.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

trait AZST_StatusIndicator
{
    #################### Public

    /**
     * Executes a status indicator.
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
    public function ExecuteStatusIndicator(int $Mode): void
    {
        $useAction = false;
        switch ($Mode) {
            case 0: //Disarmed
                $useAction = $this->ReadPropertyBoolean('UseStatusIndicatorDisarmedAction');
                $action = json_decode($this->ReadPropertyString('StatusIndicatorDisarmedAction'), true);
                break;

            case 1: //Full protection mode
            case 2: //Hull protection mode
            case 3: //Partial protection mode
            case 4: //Individual protection mode
                $useAction = $this->ReadPropertyBoolean('UseStatusIndicatorArmedAction');
                $action = json_decode($this->ReadPropertyString('StatusIndicatorArmedAction'), true);
                break;

        }
        if ($useAction && isset($action)) {
            IPS_RunAction($action['actionID'], $action['parameters']);
        }
    }

    #################### Protected

    /**
     * Checks whether the alarm zone controller should use a status indicator.
     *
     * If the alarm zone! uses an activation check or has a delayed activation,
     * the status indicator must be used by the alarm zone itself!
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection mode,
     * 2 =  Hull protection mode,
     * 3 =  Partial protection mode,
     * 4 =  Individual protection mode
     *
     * @return bool
     * false = don't use status indicator
     * true =  use status indicator
     *
     * @throws Exception
     */
    protected function CheckAlarmZoneControllerStatusIndicator(int $Mode): bool
    {
        $useAlarmZoneControllerStatusIndicator = false;
        $activationCheck = false;
        $activationDelay = false;
        switch ($Mode) {
            case 0:
                $useAction = 'UseStatusIndicatorDisarmedAction';
                break;

            case 1: //Full protection mode
                $activationCheck = $this->ReadPropertyBoolean('FullProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('FullProtectionActivationDelay');
                $useAction = 'UseStatusIndicatorArmedAction';
                break;

            case 2: //Hull protection mode
                $activationCheck = $this->ReadPropertyBoolean('HullProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('HullProtectionActivationDelay');
                $useAction = 'UseStatusIndicatorArmedAction';
                break;

            case 3: //Partial protection mode
                $activationCheck = $this->ReadPropertyBoolean('PartialProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('PartialProtectionActivationDelay');
                $useAction = 'UseStatusIndicatorArmedAction';
                break;

            case 4: //Individual protection mode
                $activationCheck = $this->ReadPropertyBoolean('IndividualProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('IndividualProtectionActivationDelay');
                $useAction = 'UseStatusIndicatorArmedAction';
                break;

        }
        if ($activationCheck || $activationDelay) {
            return false;
        }
        if (isset($useAction)) {
            if ($this->ReadPropertyBoolean($useAction)) {
                $useAlarmZoneControllerStatusIndicator = true;
            }
        }
        return $useAlarmZoneControllerStatusIndicator;
    }
}