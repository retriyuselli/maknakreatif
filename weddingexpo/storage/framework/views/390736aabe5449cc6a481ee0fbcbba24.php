<input
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
                'type' => 'hidden',
                $applyStateBindingModifiers('wire:model') => $getStatePath(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
            ->class(['fi-fo-hidden'])); ?>

/>
<?php /**PATH /home/u380354370/domains/maknaspace.com/public_html/weddingexpo/vendor/filament/forms/resources/views/components/hidden.blade.php ENDPATH**/ ?>