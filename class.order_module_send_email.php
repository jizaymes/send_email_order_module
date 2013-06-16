<?php

/**
 * Send an email
 * 
 * 
 * @author James Cornman <jizaymes@gmail.com>
 * @version $Id$
 * @package ubersmith
 * @subpackage order_module
 **/

/**
 * Send Email class
 *
 * @package ubersmith
 * @author James Cornman <jizaymes@gmail.com>
 */

class order_module_send_email extends order_module
{
        /**
         * 'interactive' determines whether or not the order module should display
         * a popup to allow the administrator to change settings, make a selection, etc.
         * If the order module is only meant to process an action without any additional
         * input, set 'interactive' to false.
         *
         * @var bool
         */
        var $interactive = true;

        /**
         * 'complete_view' indicates whether the order module has data to display after
         * processing has been completed. For example, a module that performs fraud
         * detection may have some output which would be useful to display to the administrator.
         * If you want the module to display data after completion, set 'complete_view' to true.
         * A module which simply sends an email or performs some simple task may not have any
         * data to display. In this case, set 'complete_view' to false.
         *
         * @var bool
         */
        var $complete_view = false;

        /**
         * 'reprocess' determines if a module should be able to be run more than once. This is
         * useful for modules that reach out to an external service that may require
         * an additional call, or a call once order data has been updated.
         *
         * @var bool
         */
        var $reprocess = true;

        function name()
        {
                return 'Send Email';
        }

        function replace_variables($input)
        {
                $order =& $this->order;
                $data  = $order->data();
                $info  = $order->info();

                $replace_map = array(
                        "##order_id##" => $data['order_id'],
                        "##company##" => $info['company'],
                        "##full_name##" => $info['full_name'],
                        "##first_name##" => $info['first'],
                        "##last_name##" => $info['last'],
                        "##address##" => $info['address'],
                        "##city##" => $info['city'],
                        "##state##" => $info['state'],
                        "##zip##" => $info['zip'],
                        "##phone##" => $info['phone'],
                        "##email##" => $info['email'],
                );

                foreach($replace_map as $key=>$item)
                {
                        $input = str_replace($key,$item,$input);
                }
                return $input;
        }

        function process()
        {
                $order =& $this->order;
                $data  = $order->data();
                $info  = $order->info();

                // Load any config options and parse any 

                // To addresses are comma/space delimited
                $email_to_preprocess = preg_split("/[\s,]+/",$this->config('email_to'));

                $email_from = $this->config('email_from');
                $email_subject = $this->replace_variables($this->config('email_subject'));

                $email_body = $this->replace_variables($this->config('email_body'));

                $email_reply_to = $this->config('email_reply_to');
                $email_html = $this->config('email_html');

                // Handle comma delimited email address list in To field
                $cnt = 0;

                for($cnt = 0; $cnt < count($email_to_preprocess); $cnt++)
                {
                        $email_to[] = $this->replace_variables($email_to_preprocess[$cnt]);
                }

                // Prepare a headers variable
                $headers = "";

                // Whether or not to enable HTML Email
                if($email_html)
                {
                        $headers  .= 'MIME-Version: 1.0' . "\r\n";
                        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                }

                // Set the To and From headers
                $headers .= 'From: ' . $email_from . "\r\n";

                // If there is a reply-to option set, use it.
                if($email_reply_to)
                {
                        $headers .= 'Reply-To: ' . $email_reply_to . "\r\n";
                }

                // Set the X-Mailer header
                $headers .= 'X-Mailer: PHP/' . phpversion();

                // Send the email to all recipients, set a failed flag if any of them fail.
                $cnt = 0;
                $failed = false;

                for($cnt = 0; $cnt < count($email_to); $cnt++)
                {
                        if(!mail($email_to[$cnt],$email_subject,$email_body,$headers)) {
                                echo "<PRE>Failed to send email: To: [" . $email_to[$cnt] . "]\nFrom: [$email_from]\nSubject: [$email_subject
]\nBody: [$email_body]\n\nHeaders:[$headers]\n\n";
                                $failed = true;
                        }
                }

                if(!$failed)
                {
                        return true;
                }

                return false;
        }

        /**
         * This function displays the output of the order module. Any data collected by your
         * process function and stored in the order can be displayed here. In this example,
         * we're dumping out the complete details of the order for your reference.
         *
         * @return string
         */
        function view()
        {

        }

        /**
         * This function returns an array of configuration options that will be 
         * displayed when the module is configured for your order queue. You can
         * add as many configuration items as you like. Retrieval of the configuration
         * data is shown in the view() function above.
         *
         * @return array
         */
        function config_items()
        {
                return array(
                        'email_to' => array(
                                'label'  => uber_i18n('To'),
                                'type'   => 'text',
                                'size'   => '32',
                                'default'=> '',
                        ),
                        'email_from' => array(
                                'label'  => uber_i18n('From'),
                                'type'   => 'text',
                                'size'   => '32',
                                'default'=> '',
                        ),
                        'email_subject' => array(
                                'label'  => uber_i18n('Subject'),
                                'type'   => 'text',
                                'size'   => '64',
                                'default'=> '',
                        ),
                        'email_body' => array(
                                'label'  => uber_i18n('Body'),
                                'type'   => 'textarea',
                                'rows'   => '16',
                                'cols'   => '64',
                                'default'=> '',
                        ),
                        'email_reply_to' => array(
                                'label'  => uber_i18n('Reply-To'),
                                'type'   => 'text',
                                'size'   => '32',
                                'default'=> '',
                        ),
                        'email_html' => array(
                                'label'  => uber_i18n('Send HTML Email?'),
                                'type'   => 'select',
                                'options' => array(
                                        '1' => uber_i18n('Yes'),
                                        '0' => uber_i18n('No'),
                                ),
                                'default'=> '0',
                        ),

                );
        }

}

// end of script
