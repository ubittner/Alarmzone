<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_StatusIndicator.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

trait AZ_StatusIndicator
{
    #################### Public

    /**
     * Executes a status indicator.
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection mode,
     * 2 =  Hull protection mode,
     * 3 =  Partial protection mode
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
                $useAction = $this->ReadPropertyBoolean('UseStatusIndicatorArmedAction');
                $action = json_decode($this->ReadPropertyString('StatusIndicatorArmedAction'), true);
                break;

        }
        if ($useAction && isset($action)) {
            IPS_RunAction($action['actionID'], $action['parameters']);
        }
    }
}