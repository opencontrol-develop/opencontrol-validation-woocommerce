<?php

class Messages {
    const MESSAGE_APPROVED = 'Analizado y aprobado por OpenControl';
    const MESSAGE_DENIED = 'Denegado por OpenControl';
    public static function deniedMessages($order, $matchedName, $reasons) {
        $reasonsMessage = '';
        if ($matchedName === 'matched_list') {
            $reasonsMessage = self::MESSAGE_DENIED . ' (Rejected by '.$reasons['type'].' list)';
            $order->add_order_note($reasonsMessage);
            return;
        }
        $numberReasons = count($reasons) - 1;
        $index = 0;
        foreach ($reasons as $reason) {
            $reasonsMessage = ($index === $numberReasons) ? $reasonsMessage . $reason['description'] : $reasonsMessage . $reason['description'].',';
            $index++;
        }
        $order->add_order_note(self::MESSAGE_DENIED .' ('. $reasonsMessage .')');
    }
}