<?php
$userbarText = array();
$words = new MOD_words();
?>

        <h3><?php echo $words->get('About_AtAGlance') ?></h3>
        <ul class="linklist">
            <li class="floatbox"><img src="styles/YAML/images/circle1.gif" class="float_left" alt="circle1" /> <a href="about"<?php echo ($currentSubPage === 'theidea') ? ' class="active"' : ''; ?>><?php echo $words->get('About_TheIdea') ?></a></li>
            <li class="floatbox"><img src="styles/YAML/images/circle2.gif" class="float_left" alt="circle2" /> <a href="about/thepeople"<?php echo ($currentSubPage === 'thepeople') ? ' class="active"' : ''; ?>><?php echo $words->get('About_ThePeople') ?></a></li>
            <li class="floatbox"><img src="styles/YAML/images/circle3.gif" class="float_left" alt="circle3" /> <a href="about/getactive"<?php echo ($currentSubPage === 'getactive') ? ' class="active"' : ''; ?>><?php echo $words->get('About_GetActive') ?></a></li>
        </ul>
        <h3><?php echo $words->get('MoreInfo') ?></h3>
        <ul class="linklist">
            <li><a href="press"><?php echo $words->get('PressInfoPage') ?></a></li>
            <li><a href="bod"><?php echo $words->get('BoardOfDirectorsPage') ?></a></li>
            <li><a href="http://blogs.bevolunteer.org"><?php echo $words->get('BeVolunteerBlogs') ?></a></li>
            <li><a href="terms"><?php echo $words->get('TermsPage') ?></a></li>
            <li><a href="privacy"><?php echo $words->get('PrivacyPage') ?></a></li>
        </ul>
