<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/helper/
 * @file          AZST_Action.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

trait AZST_Action
{
    #################### Public

    /**
     * Executes an action.
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection,
     * 2 =  Hull protection,
     * 3 =  Partial protection,
     * 4 =  Individual protection
     *
     * @return void
     * @throws Exception
     */
    public function ExecuteAction(int $Mode): void
    {
        $useAction = false;
        switch ($Mode) {
            case 0: //Disarmed
                $useAction = $this->ReadPropertyBoolean('UseDisarmedAction');
                $action = json_decode($this->ReadPropertyString('DisarmedAction'), true);
                break;

            case 1: //Full protection mode
                $useAction = $this->ReadPropertyBoolean('UseFullProtectionAction');
                $action = json_decode($this->ReadPropertyString('FullProtectionAction'), true);
                break;

            case 2: //Hull protection mode
                $useAction = $this->ReadPropertyBoolean('UseHullProtectionAction');
                $action = json_decode($this->ReadPropertyString('HullProtectionAction'), true);
                break;

            case 3: //Partial protection mode
                $useAction = $this->ReadPropertyBoolean('UsePartialProtectionAction');
                $action = json_decode($this->ReadPropertyString('PartialProtectionAction'), true);
                break;

            case 4: //Individual protection mode
                $useAction = $this->ReadPropertyBoolean('UseIndividualProtectionAction');
                $action = json_decode($this->ReadPropertyString('IndividualProtectionAction'), true);
                break;

        }
        if ($useAction && isset($action)) {
            IPS_RunAction($action['actionID'], $action['parameters']);
        }
    }

    #################### Protected

    /**
     * Checks whether the alarm zone controller should use an action.
     *
     * If the alarm zone! uses an activation check or has a delayed activation,
     * the action must be done by the alarm zone itself!
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection mode,
     * 2 =  Hull protection mode,
     * 3 =  Partial protection mode,
     * 4 =  Individual protection mode
     *
     * @return bool
     * false =  don't use action
     * true =   use action
     *
     * @throws Exception
     */
    protected function CheckAlarmZoneControllerAction(int $Mode): bool
    {
        $useAlarmZoneControllerAction = false;
        $activationCheck = false;
        $activationDelay = false;
        switch ($Mode) {
            case 0:
                $useAction = 'UseDisarmedAction';
                break;

            case 1: //Full protection mode
                $activationCheck = $this->ReadPropertyBoolean('FullProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('FullProtectionActivationDelay');
                $useAction = 'UseFullProtectionAction';
                break;

            case 2: //Hull protection mode
                $activationCheck = $this->ReadPropertyBoolean('HullProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('HullProtectionActivationDelay');
                $useAction = 'UseHullProtectionAction';
                break;

            case 3: //Partial protection mode
                $activationCheck = $this->ReadPropertyBoolean('PartialProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('PartialProtectionActivationDelay');
                $useAction = 'UsePartialProtectionAction';
                break;

            case 4: //Individual protection mode
                $activationCheck = $this->ReadPropertyBoolean('IndividualProtectionActivationCheck');
                $activationDelay = $this->ReadPropertyBoolean('IndividualProtectionActivationDelay');
                $useAction = 'UseIndividualProtectionAction';
                break;

        }
        if ($activationCheck || $activationDelay) {
            return false;
        }
        if (isset($useAction)) {
            if ($this->ReadPropertyString($useAction)) {
                $useAlarmZoneControllerAction = true;
            }
        }
        return $useAlarmZoneControllerAction;
    }
}