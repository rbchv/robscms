
<div class="row">
    <div class="span12">
        <div class="generalBgWrapperDiv">
            <h2><?php echo $name; ?></h2>
            <p class="error">
	           <strong><?php echo __('Error'); ?>: </strong>
               <?php echo __('The requested address was not found on this server.'); ?>
            </p>
            <p><a href="/"><?php echo __('Back'); ?></a></p>

            <?php
                if(Configure::read('debug') > 0)
                {
	               echo $this->element('exception_stack_trace');
                }
            ?>
        </div>
    </div>
</div>
