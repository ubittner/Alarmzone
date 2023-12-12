<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_Blacklist.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnusedPrivateMethodInspection */
/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

trait AZ_Blacklist
{
    /**
     * Resets the blacklist.
     *
     * @return void
     * @throws Exception
     */
    public function ResetBlacklist(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->WriteAttributeString('Blacklist', '[]');
        $this->SendDebug(__FUNCTION__, 'Die Sperrliste wurde erfolgreich zurückgesetzt!', 0);
    }

    /**
     * Deletes an element from the blacklist.
     *
     * @param int $VariableID
     * @return void
     * @throws Exception
     */
    public function DeleteElementFromBlacklist(int $VariableID): void
    {
        $elements = json_decode($this->ReadAttributeString('Blacklist'), true);
        foreach ($elements as $key => $element) {
            $data = json_decode($element, true);
            if ($data['sensorID'] == $VariableID) {
                unset($elements[$key]);
            }
        }
        $elements = array_values($elements);
        $amountBlacklist = count($elements);
        if ($amountBlacklist == 0) {
            $amountBlacklist = 1;
        }
        $this->UpdateFormField('Blacklist', 'rowCount', $amountBlacklist);
        $this->WriteAttributeString('Blacklist', json_encode($elements));
    }

    /**
     * Gets the blacklisted door and window variables.
     *
     * @return void
     * @throws Exception
     */
    public function GetBlackListedVariables(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->UpdateFormField('BlacklistConfigurationButton', 'visible', false);
        $blacklistedVariables = [];
        $blacklist = json_decode($this->ReadAttributeString('Blacklist'), true);
        $amount = count($blacklist) + 1;
        if (is_array($blacklist)) {
            foreach ($blacklist as $element) {
                $variable = json_decode($element, true);
                $id = $variable['sensorID'];
                $blacklistedVariables[] = [
                    'SensorID'          => $id,
                    'Designation'       => $variable['sensorDesignation'],
                    'VariableLocation'  => IPS_GetLocation($id)];
            }
        }
        $this->UpdateFormField('Blacklist', 'visible', true);
        $this->UpdateFormField('Blacklist', 'rowCount', $amount);
        $this->UpdateFormField('Blacklist', 'values', json_encode($blacklistedVariables));
    }

    #################### Private

    /**
     * Adds a sensor to the blacklist.
     *
     * @param int $SensorID
     * @param string $SensorDesignation
     * @return void
     * @throws Exception
     */
    private function AddSensorBlacklist(int $SensorID, string $SensorDesignation): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $blackList = json_decode($this->ReadAttributeString('Blacklist'), true);
        $blackList[] = '{"sensorID": ' . $SensorID . ',"sensorDesignation": "' . $SensorDesignation . '"}';
        $this->WriteAttributeString('Blacklist', json_encode(array_unique($blackList)));
        $this->SendDebug(__FUNCTION__, 'Der Sensor mit der ID ' . $SensorID . ' wurde zur Sperrliste hinzugefügt.', 0);
    }

    /**
     * Checks if a sensor is blacklisted.
     *
     * @param int $SensorID
     * @return bool
     * false =  not blacklisted,
     * true =   blacklisted
     *
     * @throws Exception
     */
    private function CheckSensorBlacklist(int $SensorID): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $blacklist = json_decode($this->ReadAttributeString('Blacklist'), true);
        foreach ($blacklist as $element) {
            $variable = json_decode($element, true);
            if ($variable['sensorID'] == $SensorID) {
                return true;
            }
        }
        return false;
    }
}