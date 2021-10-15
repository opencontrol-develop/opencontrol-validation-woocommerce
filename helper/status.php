<?php
class Status 
{
    const STATUSES_OPENCONTROL = [
        [
            'label'=>'AAprobado por OpenControl',
            'status'=>'wc-approved-op',
        ],
        [
            'label'=>'Denegado por OpenControl',
            'status'=>'wc-denied-op'
        ],
    ];

    public static function register_statuses_opencontrol() {
        foreach (self::STATUSES_OPENCONTROL as $status) {
            $slug = $status['status'];
            $label = $status['label'];
            register_post_status( $slug, [
                'label'                     => $label,
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>' )
            ]);
        }
    }

    public static function include_statuses_opencontrol ($orderStatuses) {
        $newStatuses = array();
        foreach ($orderStatuses as $key => $status) {
            $newStatuses[$key] = $status;
            if ('wc-processing' === $key) {
                foreach(self::STATUSES_OPENCONTROL as $opStatus){
                    $newStatuses[$opStatus['status']] = $opStatus['label'];
                }
                
            }
        }
        return $newStatuses;
    }
}