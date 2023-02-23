<?php

/**
 * @package   Astroid Framework
 * @author    Astroid Framework https://astroidframe.work
 * @copyright Copyright (C) 2023 AstroidFrame.work.
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

if (ASTROID_JOOMLA_VERSION > 3) {
    $app->getDocument()->getWebAssetManager()
        ->useScript('core')
        ->useScript('keepalive')
        ->useScript('field.passwordview');
} else {
    JHtml::_('behavior.keepalive');
    JHtml::_('bootstrap.tooltip');
}

Text::script('JSHOWPASSWORD');
Text::script('JHIDEPASSWORD');
?>
<form id="login-form-<?php echo $module->id; ?>" class="mod-login" action="<?php echo Route::_('index.php', true); ?>" method="post">
    <?php if ($params->get('pretext')) : ?>
        <div class="pretext">
            <?php echo $params->get('pretext'); ?>
        </div>
    <?php endif; ?>
    <div class="mod-login__username form-group">
        <?php if (!$params->get('usetext', 0)) : ?>
            <div class="input-group">
                <input id="modlgn-username-<?php echo $module->id; ?>" type="text" name="username" class="form-control" autocomplete="username" placeholder="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>">
                <label for="modlgn-username-<?php echo $module->id; ?>" class="visually-hidden"><?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
                <span class="input-group-text" title="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>">
                    <span class="fas fa-user" aria-hidden="true"></span>
                </span>
            </div>
        <?php else : ?>
            <label for="modlgn-username-<?php echo $module->id; ?>"><?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
            <input id="modlgn-username-<?php echo $module->id; ?>" type="text" name="username" class="form-control" autocomplete="username" placeholder="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>">
        <?php endif; ?>
    </div>

    <div class="mod-login__password form-group">
        <?php if (!$params->get('usetext', 0)) : ?>
            <div class="input-group">
                <input id="modlgn-passwd-<?php echo $module->id; ?>" type="password" name="password" autocomplete="current-password" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>">
                <label for="modlgn-passwd-<?php echo $module->id; ?>" class="visually-hidden"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
                <button type="button" class="btn btn-secondary input-password-toggle">
                    <span class="fas fa-eye" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('JSHOWPASSWORD'); ?></span>
                </button>
            </div>
        <?php else : ?>
            <label for="modlgn-passwd-<?php echo $module->id; ?>"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
            <input id="modlgn-passwd-<?php echo $module->id; ?>" type="password" name="password" autocomplete="current-password" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>">
        <?php endif; ?>
    </div>

    <?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
        <div class="mod-login__remember form-group">
            <div id="form-login-remember-<?php echo $module->id; ?>" class="form-check">
                <label class="form-check-label">
                    <input type="checkbox" name="remember" class="form-check-input" value="yes">
                    <?php echo Text::_('MOD_LOGIN_REMEMBER_ME'); ?>
                </label>
            </div>
        </div>
    <?php endif; ?>

    <?php if (ASTROID_JOOMLA_VERSION > 3) { ?>
        <?php foreach($extraButtons as $button):
            $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
                return substr($key, 0, 5) == 'data-';
            });
            ?>
            <div class="mod-login__submit form-group">
                <button type="button"
                        class="btn btn-secondary w-100 mt-4 <?php echo $button['class'] ?? '' ?>"
                <?php foreach ($dataAttributeKeys as $key): ?>
                    <?php echo $key ?>="<?php echo $button[$key] ?>"
                <?php endforeach; ?>
                <?php if ($button['onclick']): ?>
                    onclick="<?php echo $button['onclick'] ?>"
                <?php endif; ?>
                title="<?php echo Text::_($button['label']) ?>"
                id="<?php echo $button['id'] ?>"
                >
                <?php if (!empty($button['icon'])): ?>
                    <span class="<?php echo $button['icon'] ?>"></span>
                <?php elseif (!empty($button['image'])): ?>
                    <?php echo $button['image']; ?>
                <?php elseif (!empty($button['svg'])): ?>
                    <?php echo $button['svg']; ?>
                <?php endif; ?>
                <?php echo Text::_($button['label']) ?>
                </button>
            </div>
        <?php endforeach; ?>
    <?php } ?>
    <div class="mod-login__submit form-group">
        <button type="submit" name="Submit" class="btn btn-primary w-100"><?php echo Text::_('JLOGIN'); ?></button>
    </div>

    <?php $usersConfig = ComponentHelper::getParams('com_users'); ?>
    <ul class="mod-login__options list-group">
        <li class="list-group-item">
            <a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>">
                <?php echo Text::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
        </li>
        <li class="list-group-item">
            <a href="<?php echo Route::_('index.php?option=com_users&view=remind'); ?>">
                <?php echo Text::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
        </li>
        <?php if ($usersConfig->get('allowUserRegistration')) : ?>
            <li class="list-group-item">
                <a href="<?php echo Route::_($registerLink); ?>">
                    <?php echo Text::_('MOD_LOGIN_REGISTER'); ?> <span class="icon-register" aria-hidden="true"></span></a>
            </li>
        <?php endif; ?>
    </ul>
    <input type="hidden" name="option" value="com_users" />
    <input type="hidden" name="task" value="user.login" />
    <input type="hidden" name="return" value="<?php echo $return; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
    <?php if ($params->get('posttext')) : ?>
        <div class="mod-login__posttext posttext">
            <p><?php echo $params->get('posttext'); ?></p>
        </div>
    <?php endif; ?>
</form>