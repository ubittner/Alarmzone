<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_AcknowledgementTone.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

trait AZ_AcknowledgementTone
{
    #################### Public

    /**
     * Executes an acknowledgement tone.
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Armed
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

            case 1: //Armed
                $useAction = $this->ReadPropertyBoolean('UseAcknowledgementToneArmedAction');
                $action = json_decode($this->ReadPropertyString('AcknowledgementToneArmedAction'), true);
                break;

        }
        if ($useAction && isset($action)) {
            IPS_RunAction($action['actionID'], $action['parameters']);
        }
    }
}