<div class="row">
    <div class="col-12 text-center text-md-left">
        <h2 class="m-0 d-inline"><strong>
                <?php if ($this->passedAway == 'PassedAway') {
                    echo $words->get('ProfileInMemoriam', $member->Username);
                } else {
                    echo $member->Username;
                } ?>
            </strong>

            <?php if (!$this->passedAway) : ?>
                <?php
                $icons = array();
                if (strstr($member->TypicOffer, "CanHostWeelChair")) {
                    $icons[] = '<img src="images/icons/wheelchairblue.png" ' .
                        'class="mb-2" ' .
                        'alt="' . $words->getSilent('wheelchair') . '" ' .
                        'title="' . $words->getSilent('CanHostWheelChairYes') . '" />';
                }

                $icons[] = '<img src="images/icons/' . $member->Accomodation . '.png"' .
                    ' class="mb-2"' .
                    ' alt="' . $words->getSilent($member->Accomodation) . '"' .
                    ' title="' . $words->getSilent('CanOffer' . $member->Accomodation) . '" />';

                for ($ii = 0; $ii < count($icons); $ii++) {
                    echo $icons[$ii];
                }
                ?>
                <?= $words->flushBuffer() ?>
            <?php endif; ?>
        </h2>
    </div>
</div>
<div class="row align-items-center">
    <div class="col-12 col-sm-6">
        <?php
        $picture_url = 'members/avatar/' . $member->Username;
        ?>
        <div class="text-center text-md-left">
            <?php if ($this->useLightbox) { ?>
                <a class="d-md-none" href="<?= $picture_url . '/original' ?>" data-toggle="lightbox"
                   data-always-show-close="true" data-type="image"
                   data-title="<?= $words->getbuffered('profile.picture.title'); ?>">
                    <img class="profileimg avatar-160 d-md-none" src="<?= $picture_url . '/160' ?>"
                         alt="<?= $words->get('profile.picture.title'); ?>">
                </a>
            <?php } else { ?>
                <a href="members/<?= $member->Username; ?>">
                    <img class="profileimg avatar-160 d-md-none" src="<?= $picture_url . '/160' ?>"
                         alt="<?= $words->getbuffered('profile.picture.title'), $member->Username; ?>">
                </a>
            <?php } ?>

            <?php if ($member->name() != '') {
                echo "<h3>" . $member->name() . "</h3>";
            } ?> <!-- user's name -->

            <h5>
                <?php
                // The "Hong Kong solution": Only display and link country.
                if ($member->region() == '' && $member->city() == $member->country()) :
                    ?>
                    <strong><a
                            href="places/<?php echo urlencode($member->country()) . "/" . urlencode($member->countrycode()); ?>"><?php echo $member->country(); ?></a></strong>
                <?php
                // In case of missing parent in Geonames DB: Only display city and country. Don't link city.
                elseif ($member->region() == '') :
                    ?>
                    <strong><?php echo urlencode($member->city()); ?></strong>, <a href="places/<?php
                echo urlencode($member->country()) . "/" . urlencode($member->countryCode()); ?>"><?php echo $member->country(); ?></a>
                <?php
                // For every other city display normal path. Don't show region if it has the same name as city.
                else :
                    ?>
                    <strong><a
                        href="places/<?php echo urlencode($member->country()) . "/" . urlencode($member->countrycode())
                            . "/" . urlencode($member->region()) . "/" . urlencode($member->regioncode()) . "/"
                            . urlencode($member->city) . "/" . $member->IdCity; ?>"><?php echo $member->city(); ?></a></strong><?php if ($member->region() != $member->city()) : ?>,
                    <a
                    href="places/<?php echo urlencode($member->country()) . "/" . urlencode($member->countryCode()) . "/"
                        . urlencode($member->region()) . "/" . urlencode($member->regioncode()); ?>"><?php echo $member->region(); ?></a><?php endif; ?>,
                    <a href="places/<?php echo urlencode($member->country()) . "/" . urlencode($member->countryCode()); ?>"><?php echo $member->country(); ?></a>
                <?php
                endif;
                ?>
            </h5><!-- location -->

            <div class="o-form-group form-inline small"><?php echo $this->statusForm($member); ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 teaser-border text-center text-md-left">

        <?php if ($occupation != null) {
            echo '<p class="h5">' . $occupation . '</p>';
        } ?>
        <!-- occupation -->

        <p class="mb-2">
            <?php
            echo $agestr;
            $strGender = MOD_layoutbits::getGenderTranslated($member->Gender, $member->HideGender, true);
            if (!empty($strGender)) {
                echo ', ' . $strGender;
            }
            echo '<br>';
            ?>
            <?php if ($this->loggedInMember) : ?>
                <?php echo '<span class="font-weight-bold">' . $words->get("MemberSince") . ': </span>';
                if (strtotime($member->created) > strtotime('-1 week')) {
                    echo $words->get("LastLoginPrivacy");
                } else {
                    echo $layoutbits->ago(strtotime($member->created));
                }
                echo $this->memberSinceDate($member);
                echo '<br><span class="font-weight-bold">' . $words->get("LastLogin") . ': </span>';
                if (strtotime($member->LastLogin) > strtotime('-1 week')) {
                    echo $words->get("LastLoginPrivacy");
                } else {
                    echo $layoutbits->ago(strtotime($member->LastLogin));
                }
                echo $this->lastLoginDate($member);
                ?>
            <?php endif; ?>
        </p>
    </div>
</div> <!-- profile header -->

<?php
if (get_class($this) == 'EditMyProfilePage' || get_class($this) == 'EditProfilePage') $urlstring = 'editmyprofile';
if (get_class($this) == 'ProfilePage' || get_class($this) == 'MyProfilePage') $urlstring = 'members/' . $member->Username;
if (isset($urlstring)) { ?>
    <div class="row">
        <div class="col-12">
            <?php require 'profileversion.php'; ?>
        </div>
    </div>
<?php } ?>
