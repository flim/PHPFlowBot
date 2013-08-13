<?php

class PFBBaseFlowRegister
{
    /**
     * @description Check the corect format of a given flow
     * @param $flow
     * @return bool
     */
    static public function checkFlowFormat($flow)
    {
        if (!isset($flow)) {
            return false;
        }
        if (PFBBaseFlowRegister::isMultipleFlowFormat($flow)) {
            if (count($flow) == 0) {
                return false;
            }
            foreach ($flow as $f) {
                if (!PFBBaseFlowRegister::isUniqueFlowFormat($f))
                    return false;
            }
        } else if (!PFBBaseFlowRegister::isUniqueFlowFormat($flow))
            return false;
        return true;
    }

    /**
     * @description Check if multiple format flow is given
     * @param $flow
     * @return bool
     */
    static public function isMultipleFlowFormat($flow)
    {
        return (is_array($flow) && !isset($flow['flow']));
    }

    /**
     * @description Check if unique format flow is given
     * @param $flow
     * @return bool
     */
    static public function isUniqueFlowFormat($flow)
    {
        return (isset($flow["flow"]) && isset($flow["token"]));
    }

}