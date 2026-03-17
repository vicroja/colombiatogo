<?php foreach ($messages as $msg): ?>
    <?php $isIncoming = ($msg->direction === 'incoming'); ?>
    <div class="d-flex <?= $isIncoming ? 'justify-content-start' : 'justify-content-end' ?> mb-3">
        <div class="p-3 shadow-sm <?= $isIncoming ? 'bg-white text-dark' : 'bg-success text-white' ?>"
             style="max-width: 75%; border-radius: 15px; position: relative;">

            <p class="mb-1"><?= nl2br(htmlspecialchars($msg->message_body)) ?></p>

            <small class="<?= $isIncoming ? 'text-muted' : 'text-light' ?> d-block text-right" style="font-size: 0.7rem;">
                <?= date('H:i', strtotime($msg->created_at)) ?>
                <?php if (!$isIncoming): ?>
                    <i class="fas fa-check-double ml-1"></i>
                <?php endif; ?>
            </small>
        </div>
    </div>
<?php endforeach; ?>