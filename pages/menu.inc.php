	<div id="rex-navi-page">
        <ul>
          <li><a href="?page=<?php print  $REX['ADDON375']['addon_name']; ?>&amp;subpage=newsletter" tabindex="18"<?php print  $REX['ADDON375']['postget']['subpage'] == 'newsletter' ? ' class="rex-subpage-active"' : ''; ?>>&nbsp;<?php print  $REX['ADDON375']['I18N']->msg('menu_newsletter'); ?></a></li>
          <li><a href="?page=<?php print  $REX['ADDON375']['addon_name']; ?>&amp;subpage=groups" tabindex="19"<?php print  $REX['ADDON375']['postget']['subpage'] == 'groups' ? ' class="rex-subpage-active"' : ''; ?>>&nbsp;<?php print  $REX['ADDON375']['I18N']->msg('menu_groups'); ?></a></li>
          <li><a href="?page=<?php print  $REX['ADDON375']['addon_name']; ?>&amp;subpage=user" tabindex="20"<?php print  $REX['ADDON375']['postget']['subpage'] == 'user' ? ' class="rex-subpage-active"' : ''; ?>>&nbsp;<?php print  $REX['ADDON375']['I18N']->msg('menu_user'); ?></a></li>
          <li><a href="?page=<?php print  $REX['ADDON375']['addon_name']; ?>&amp;subpage=archive" tabindex="21"<?php print  $REX['ADDON375']['postget']['subpage'] == 'archive' || $REX['ADDON375']['postget']['subpage'] == 'archiveout' ? ' class="rex-subpage-active"' : ''; ?>><?php print  $REX['ADDON375']['I18N']->msg('menu_archive'); ?></a></li>
          <li><a href="?page=<?php print  $REX['ADDON375']['addon_name']; ?>&amp;subpage=import" tabindex="22"<?php print  $REX['ADDON375']['postget']['subpage'] == 'import' ? ' class="rex-subpage-active"' : ''; ?>>&nbsp;<?php print  $REX['ADDON375']['I18N']->msg('menu_import'); ?></a></li>
          <li><a href="?page=<?php print  $REX['ADDON375']['addon_name']; ?>&amp;subpage=config" tabindex="23"<?php print  $REX['ADDON375']['postget']['subpage'] == 'config' ? ' class="rex-subpage-active"' : ''; ?>>&nbsp;<?php print  $REX['ADDON375']['I18N']->msg('menu_config'); ?></a></li>
          <li><a href="?page=<?php print  $REX['ADDON375']['addon_name']; ?>&amp;subpage=help" tabindex="24"<?php print  $REX['ADDON375']['postget']['subpage'] == 'help' ? ' class="rex-subpage-active"' : ''; ?>>&nbsp;<?php print  $REX['ADDON375']['I18N']->msg('menu_help'); ?></a></li>
        </ul>
	</div>
