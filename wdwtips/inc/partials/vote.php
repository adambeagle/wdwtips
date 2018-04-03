<?php
  /**
   * vote.php
   * Author: Adam Beagle
   *
   * Vote buttons partial
   */
?>
        <div class="vote">
          <div class="vote-up">
            <a class="vote-btn<?= $tip->vote ? ' voted' : '' ?>" href="#" role="button"<?= $tip->vote ? ' aria-pressed ' : ' ' ?>data-id="<?= $tip->id ?>">
<?php
  include 'arrowup.php';
?>
            </a>
          </div>
          <div class="score" data-id="<?= $tip->id ?>"><?= $tip->score ?></div>
          <div class="vote-down">
            <a class="vote-btn<?= $tip->vote === false ? ' voted' : '' ?>" href="#" role="button"<?= $tip->vote === false ? ' aria-pressed ' : ' ' ?>data-id="<?= $tip->id ?>">
              &#x25bc;
            </a>
          </div>
        </div>