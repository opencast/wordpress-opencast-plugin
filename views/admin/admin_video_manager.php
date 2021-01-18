<div class="wrap oc-admin-wrapper">
    <h1>Opencast Plugin</h1>
    <div class='oc-admin-video-list' data-url='<?php echo admin_url('admin-ajax.php') ?>'>
        <div class="oc-filters-container">
            <div class="oc-admin-video-action">
                <select style='width: 60%' name='oc-filter-action-select' class='oc-select2' placeholder='<?php echo __('Actions'); ?>'>
                    <option></option>
                    <option vlaue='delete'><?php echo __('Delete'); ?></option>
                </select>
            </div>
            <div class="oc-admin-video-search">
                <input type="text" style='width: 60%' name="oc-filter-search-input regular-text" placeholder='<?php echo __('Search'); ?>'>
            </div>
            <div class="oc-admin-video-limit">
                <select style='width: 60%' name='oc-filter-limit-select' class='oc-select2'>
                    <option selected vlaue='10'>10</option>
                    <option vlaue='20'>20</option>
                    <option vlaue='50'>50</option>
                    <option vlaue='100'>100</option>
                </select>
            </div>
        </div>
        <div class="oc-admin-list-container">
            <table class='oc-admin-list-table'>
                <thead>
                    <tr>
                        <th class='' id='bulk-selection'>
                            <input type="checkbox" class='oc-table-bulk bulk-parent'>
                        </th>
                        <th class='oc-table-sortable' id='title'>
                            <?php echo __('Title'); ?>
                            <span>
                                a
                            </span>
                        </th>
                        <th class='oc-table-sortable' id='presenters'>
                            <?php echo __('Presenter(s)'); ?>
                        </th>
                        <th class='oc-table-sortable' id='date'>
                            <?php echo __('Date'); ?>
                        </th>
                        <th class='oc-table-sortable' id='start'>
                            <?php echo __('Start'); ?>
                        </th>
                        <th class='oc-table-sortable' id='stop'>
                            <?php echo __('Stop'); ?>
                        </th>
                        <th class='oc-table-sortable' id='location'>
                            <?php echo __('Location'); ?>
                        </th>
                        <th class='' id='published'>
                            <?php echo __('Published'); ?>
                        </th>
                        <th class='oc-table-sortable' id='status'>
                            <?php echo __('Status'); ?>
                        </th>
                        <th class='' id='actions'>
                            <?php echo __('Actions'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>