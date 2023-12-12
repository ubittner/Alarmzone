<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_AlarmProtcol.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnusedPrivateMethodInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

trait AZ_AlarmProtocol
{
    #################### Private

    /**
     * Updates the alarm protocol.
     *
     * @param string $LogText
     * @param int $LogType
     * 0 =  event message,
     * 1 =  state message,
     * 2 =  alarm message
     *
     * @return void
     * @throws Exception
     */
    private function UpdateAlarmProtocol(string $LogText, int $LogType): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        $id = $this->ReadPropertyInteger('AlarmProtocol');
        if ($id > 1 && @IPS_ObjectExists($id)) {
            @AP_UpdateMessages($id, $LogText, $LogType);
        }
    }
}