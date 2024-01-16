<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_Action.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

trait AZ_Action
{
    #################### Public

    /**
     * Executes an action.
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection
     * 2 =  Hull protection,
     * 3 =  Partial protection
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

        }
        if ($useAction && isset($action)) {
            IPS_RunAction($action['actionID'], $action['parameters']);
        }
    }
}