<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU General Public License, version 2 (GPLv2)
 * Copyright 2001 - 2015 Ampache.org
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License v2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */

class AmpachePaypal {
    public $name           = 'Paypal';
    public $categories     = 'user';
    public $description    = 'Paypal donation button on user page';
    public $url            = '';
    public $version        = '000001';
    public $min_ampache    = '370034';
    public $max_ampache    = '999999';

    // These are internal settings used by this class, run this->load to
    // fill them out
    private $user;
    private $business;
    private $currency_code;

    /**
     * Constructor
     * This function does nothing...
     */
    public function __construct() {
        return true;
    }

    /**
     * install
     * This is a required plugin function. It inserts our preferences
     * into Ampache
     */
    public function install() {
        // Check and see if it's already installed
        if (Preference::exists('paypal_business')) { return false; }

        Preference::insert('paypal_business','Paypal ID','',25,'string','plugins');
        Preference::insert('paypal_currency_code','Paypal Currency Code','USD',25,'string','plugins');

        return true;
    }

    /**
     * uninstall
     * This is a required plugin function. It removes our preferences from
     * the database returning it to its original form
     */
    public function uninstall() {
        Preference::delete('paypal_business');
        Preference::delete('paypal_currency_code');

        return true;
    }

    /**
     * upgrade
     * This is a recommended plugin function
     */
    public function upgrade() {
        return true;
    }

    /**
     * display_user_field
     * This display the module in user page
     */
    public function display_user_field(library_item $libitem = null) {
        $name = ($libitem != null) ? $libitem->get_fullname() : (T_('User') . " `" . $this->user->fullname . "` " . T_('on') . " " . AmpConfig::get('site_title'));
        $lang = substr(AmpConfig::get('lang'), 0, 2);
        if (empty($lang)) $lang = 'US';

        echo "<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>\n";
        echo "<input type='hidden' name='cmd' value='_donations'>\n";
        echo "<input type='hidden' name='business' value='" . scrub_out($this->business) . "'>\n";
        echo "<input type='hidden' name='lc' value='" . $lang . "'>\n";
        echo "<input type='hidden' name='item_name' value='" . $name . "'>\n";
        echo "<input type='hidden' name='no_note' value='0'>\n";
        echo "<input type='hidden' name='currency_code' value='" . scrub_out($this->currency_code) . "'>\n";
        echo "<input type='hidden' name='bn' value='PP-DonationsBF:btn_donate_SM.gif:NonHostedGuest'>\n";
        echo "<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>\n";
        echo "<img alt='' border='0' src='https://www.paypalobjects.com/fr_XC/i/scr/pixel.gif' width='1' height='1'>\n";
        echo "</form>\n";
    }

    /**
     * load
     * This loads up the data we need into this object, this stuff comes
     * from the preferences.
     */
    public function load($user) {
        $this->user = $user;
        $user->set_preferences();
        $data = $user->prefs;

        $this->business = trim($data['paypal_business']);
        if (!strlen($this->business)) {
            debug_event($this->name,'No Paypal ID, user field plugin skipped','3');
            return false;
        }

        $this->currency_code = trim($data['paypal_currency_code']);
        if (!strlen($this->currency_code)) {
            $this->currency_code = 'USD';
        }

        return true;
    }
}
