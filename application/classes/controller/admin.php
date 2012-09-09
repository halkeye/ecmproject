<?php defined('SYSPATH') OR die('No Direct Script Access');

/**
 * Admin Controller
 *
 * All admin functionality is implemented here.
 * @author Stephen Tiu <stt@sfu.ca>
 * @version 1.1
 * @package ecm
 *
 * TODO: Generize manage/create/edit.
 */


class Controller_Admin extends Base_MainTemplate
{
    const ROWS_PER_PAGE = 10;
    const ADMIN_USERGROUP = 3;

    function before()
    {
        $ret = parent::before();
        $this->requireLogin();
        $this->requirePermission('admin'); //Force requirement of full administrative access minimum.
        return $ret;
    }

    function action_index()
    {
        $this->template->title =        __('Administration');
        $this->template->heading =      __('Administration');
        $this->template->subheading =   __('Manage the various parts of this system.');

        $this->template->content = new View('admin/main');
    }

    /*
    * Convention CRUD actions
    */
    function action_manageConventions() {
        // Set headers
        $this->template->title =        __('Admin: Event List');
        $this->template->heading =      __('Admin: Event List');
        $this->template->subheading =   __('Create, modify and delete events.');

        $this->genericManageEntity('Convention', 'Conventions');
    }
    function action_createConvention() {
        $this->template->title =        __('Admin: Create an Event');
        $this->template->heading =      __('Admin: Create an Event');
        $this->template->subheading =   __('Create a new event');

        $conv = ORM::factory('Convention');
        $fields = $conv->default_fields;
        $post = $conv->as_array();

        if ($post = $this->request->post())
        {
            $conv->values($post);
            try {
                $conv->save();
                $this->addMessage(__('Created a new event, ') . $conv->name);
                $this->request->redirect('admin/manageConventions');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong ... but it's not your fault. Contact the system maintainer please!");
            }
        }
        $this->template->content = new View('admin/Convention', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => 'createConvention'
        ));
    }
    function action_editConvention($id = NULL) {
        /* If no ID or bad ID defined, kill it with fire. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');

        $conv = ORM::factory('Convention', $id);
        $fields = $conv->default_fields;

        /* If pass is not loaded, we have a problem */
        if (!$conv->loaded())
        {
            $errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';
            $this->request->redirect('admin/manageConventions');
        }

        $this->template->title =        __('Admin: Editing ' . $conv->name); //Escape output?
        $this->template->heading =      __('Admin: Editing ' . $conv->name);
        $this->template->subheading =   __('Edit the details of this event');

        if ($post = $this->request->post())
        {
            $conv->values($post);
            try {
                $conv->save();
                $this->addMessage('Successfully edited ' . $conv->name);
                $this->request->redirect('admin/manageConventions');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong ... but it's not your fault. Contact the system maintainer please!");
            }
        }

        $this->template->content = new View('admin/Convention', array(
            'row' => $conv->as_array(),
            'fields' => $fields,
            'callback' => "editConvention/$id"
        ));
    }
    function action_deleteConvention($id = NULL) {
        Controller_Admin::__delete($id, 'Convention', 'deleteConvention', 'manageConventions');
    }

    /*
    * Ticket/Pass CRUD actions
    */
    function action_managePasses($convention_id = NULL) {
        // Set headers
        $this->template->title =        __('Admin: Manage Tickets');
        $this->template->heading =      __('Admin: Manage Tickets');
        $this->template->subheading =   __('Create, modify and delete tickets associated with events.');

        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');
        $convention_id = $this->session->get_once('admin_convention_id', Controller_Admin::getConventionId($convention_id, $crows) );

        $opt_conditions = array('convention_id' => $convention_id);
        $opt_viewAttributes = array('convention_id' => $convention_id, 'crows' => $crows);
        $this->genericManageEntity('Pass', 'Passes', $opt_conditions, $opt_viewAttributes);
    }
    function action_createPass()  {
        // Set headers
        $this->template->title =        __('Admin: Create a Ticket');
        $this->template->heading =      __('Admin: Create a Ticket');
        $this->template->subheading =   __('Create a ticket associated to an event');

        $pass = ORM::factory('Pass');
        $fields = $pass->default_fields;
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');
        $fields['convention_id']['values'] = $crows;

        if ($post = $this->request->post())
        {
            $post['startDate'] = ECM_Form::parseSplitDate($post, 'startDate');
            $post['endDate'] = ECM_Form::parseSplitDate($post, 'endDate');

            $extra_validation = Validation::Factory($post);
            $extra_validation->rule('tickets_total', 'numeric');

            $post['isPurchasable'] = empty($post['isPurchasable']) ? 0 : $post['isPurchasable'];
            $post['requireDOB'] = empty($post['requireDOB']) ? 0 : $post['requireDOB'];

            $pass->values($post);
            $pass->tickets_total = $post['tickets_total'];

            try {
                $pass->save($extra_validation);
                $this->addMessage( __('Created a new ticket, ') . $pass->name);
                $this->session->set('admin_convention_id', $post['convention_id']);
                $this->request->redirect('admin/managePasses');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
        }
        else
        {
            $post = $pass->as_array();
            $post['tickets_total'] = '';
        }

        $this->template->content = new View('admin/Pass', array(
            'crows' => $crows,
            'row' => $post,
            'fields' => $fields,
            'callback' => 'createPass'
        ));
    }
    function action_editPass($id = NULL) {
        // Set headers

        /* If no ID or bad ID defined, kill it with fire. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');

        $pass = ORM::factory('Pass',$id);
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');
        $fields = $pass->default_fields;
        $fields['convention_id']['values'] = $crows;

        /* If pass is not loaded, we have a problem */
        if (!$pass->loaded())
        {
            $errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';
            $this->request->redirect('admin/managePasses');
        }

        $this->template->title =        __('Admin: Editing ticket "' . $pass->name . '"');
        $this->template->heading =      __('Admin: Editing ticket "' . $pass->name . '"');
        $this->template->subheading =   __('Edit the details of this ticket.');

        if ($post = $this->request->post())
        {
            $post['startDate'] = ECM_Form::parseSplitDate($post, 'startDate');
            $post['endDate'] = ECM_Form::parseSplitDate($post, 'endDate');

            $extra_validation = Validation::Factory($post);
            $extra_validation->rule('tickets_total', 'numeric');

            $post['isPurchasable'] = empty($post['isPurchasable']) ? 0 : $post['isPurchasable'];
            $post['requireDOB'] = empty($post['requireDOB']) ? 0 : $post['requireDOB'];

            $pass->values($post);

            try {
                $pass->save();
                $this->addMessage('Successfully edited ' . $pass->name);
                $this->session->set('admin_convention_id', $post['convention_id']);
                $this->request->redirect('admin/managePasses');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
            }
        }
        else {
            $post = $pass->as_array();
        }
        $this->template->content = new View('admin/Pass', array(
            'crows' => $crows,
            'row' => $post,
            'fields' => $fields,
            'callback' => "editPass/$id"
        ));
    }
    function action_deletePass($id = NULL) {
        Controller_Admin::__delete($id, 'Pass', 'deletePass', 'managePasses');
    }

    /*
    * Registration CRUD actions
    */
    function action_manageRegistrations($convention_id = NULL) {
        // Set headers
        $this->template->title =        __('Admin: Manage Registrations');
        $this->template->heading =      __('Admin: Manage Registrations');
        $this->template->subheading =   __('View the list of registrations (per event).');

        // Get the list of conventions and convention id's.
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');
        $convention_id = Controller_Admin::getConventionId($convention_id, $crows);

        // Optional parameters
        $opt_conditions = array('convention_id' => $convention_id);
        $opt_viewAttributes = array('convention_id' => $convention_id, 'crows' => $crows);
        $this->genericManageEntity('Registration', 'Registrations', $opt_conditions, $opt_viewAttributes);
    }
    function action_createRegistration() {
        // Set headers
        $this->template->title =        __('Admin: Create Registration(s)');
        $this->template->heading =      __('Admin: Create Registration(s)');
        $this->template->subheading =   __('Create registrations for an existing event.');

        $reg = ORM::factory('Registration');
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');

        $fields = $reg->formo_defaults;
        $fields['convention_id'] = array( 'type'  => 'select', 'label' => 'Convention', 'required'=>true );
        $fields['convention_id']['values'] = $crows;

        if ($post = $this->request->post())
        {
            if (Model_Convention::validConvention($post['convention_id']))
            {
                $this->session->set('admin_convention_id', $post['convention_id']);
                $this->request->redirect('admin/createRegistration2/');
            }
            else
            {
                $this->addError("You must select a valid convention before you can continue.");
            }
        }

        $this->template->content = new View('admin/Registration', array(
            'row' => $reg->as_array(),
            'fields' => $fields,
            'callback' => 'createRegistration'
        ));
    }
    function action_createRegistration2() {
        $post = $this->request->post();
        $alt_convention_id = $this->hasValue($post, 'convention_id') ? $post['convention_id'] : 0;
        $convention_id = $this->session->get_once('admin_convention_id', $alt_convention_id);

        if (!$convention_id && !isset($post['convention_id']))
        {
            $this->addError("You must select a valid convention before you can continue");
            $this->request->redirect('admin/createRegistration');
        }

        $reg = ORM::factory('Registration');
        $con = ORM::factory('Convention', $convention_id);
        if (! $con->loaded() )
        {
            $this->addError('This convention appears to have disappeared into the nether. Please select again.');
            $this->request->redirect('admin/createRegistration/');
        }

        $fields = $reg->formo_defaults;
        $fields['pickupStatus']['values'] = array(
            0 => __("Not Picked Up"),
            1 => __("Picked Up"),
        );

        $locations = ORM::Factory('Location')->find_all_non_reserved()->as_array('id', 'prefix');
        if ( !$locations )
        {
            $this->addError('No purchase locations defined! Please define some locations before continuing.');
            $this->request->redirect('admin/createRegistration/');
        }

        $passes = ORM::Factory('Pass')->where('convention_id', '=', $convention_id)->find_all()->as_array('id', 'name');

        //Check if passes exist.
        if (!$passes) {
            $this->addError("This event has no tickets! Create some tickets first before you create registrations.");
            $this->request->redirect('admin/createRegistration');
        }

        $fields['pass_id']['values'] = $passes;
        $fields['status']['values']  = Model_Registration::getStatusValues();
        $fields['comp_loc']['values'] = $locations;
        $fields['convention_id'] = $convention_id;

        $this->template->title =        __('Admin: Create Registration(s) for ') . $con->name;
        $this->template->heading =      __('Admin: Create Registration(s) for ') . $con->name;
        $this->template->subheading =   __('Create registrations for ') . $con->name;

        if ($post)
        {
            $post['convention_id'] = $post['convention_id'];
            $post['location_id'] = $post['comp_loc'];
            $post['reg_id'] = $post['comp_id'];
            $reg->values($post);
            $extra_validation = $this->validateEmailOrPhone($post);

            try {
                $reg->save($extra_validation);
                $this->addMessage( __('Created a new registration, ') . $reg->getRegID());
                $this->session->set('admin_convention_id', $post['convention_id']);

                $new_reg = ORM::factory('Registration');
                $new_reg->pass_id = $reg->pass_id;
                $comp_loc = $post['comp_loc'];

                $post = $new_reg->as_array();
                $post['comp_loc'] = $comp_loc;
                $post['status'] = Model_Registration::STATUS_PAID;
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
        }
        else
        {
            $post = $reg->as_array();
            $post['convention_id'] = $convention_id;
            $post['status'] = Model_Registration::STATUS_PAID;
        }

        /* Full registration at this step. */
        $this->template->content = new View('admin/Registration2', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => "createRegistration2"
        ));
    }
    function action_editRegistration($rid = NULL) {
        //Not allowed to be lazy in checking input here.
        if ( (!isset($rid) || !is_numeric($rid) || $rid <= 0) )
            die("You're not allowed to be here!");

        $post = $this->request->post();
        $reg = ORM::factory('Registration',$rid);
        if (! $reg->loaded() )
        {
            $this->addError('Grace says this registration does not exist.');
            $this->request->redirect('admin/manageRegistrations/');
        }

        $fields = $reg->formo_defaults;
        $fields['convention_name'] = ORM::Factory('Convention', $reg->convention_id)->name;
        $fields['status']['values']  = Model_Registration::getStatusValues();
        $fields['pickupStatus']['values'] = array(
            0 => __("Not Picked Up"),
            1 => __("Picked Up"),
        );
        $passes = ORM::Factory('Pass')->where("convention_id", '=', $reg->convention_id)->find_all()->as_array('id', 'name');

        //Check if passes exist.
        if (!$passes) {
            $this->addError("This event has no tickets! Create some passes first before you create registrations.");
            $this->request->redirect('admin/createRegistration');
        }

        $fields['pass_id']['values'] = $passes;

        if ($post)
        {
            //Disallow changing of convention and registration id's.
            unset($post['convention_id']);
            unset($post['reg_id']);
            $reg->values($post);
            $extra_validation = $this->validateEmailOrPhone($post);

            //No tickets are being allocated or deleted so no call necessary to ticket allocation methods.
            try {
                $reg->save();
                $this->addMessage( __('Successfully edited registration, ') . $reg->getRegID());
                $this->session->set('admin_convention_id', $reg->convention_id);
                $this->request->redirect('admin/manageRegistrations');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
        } else {
            $post = $reg->as_array();
        }

        $post['reg_id'] = $reg->getRegID();
         /* Full registration at this step. */
        $fields['convention_id'] = $reg->convention_id;
        $this->template->content = new View('admin/EditRegistration', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => "editRegistration/$rid",
            'emailCallback' => "editRegistration_sendEmail/$rid",
        ));

    }
    function action_editRegistration_sendEmail($id)
    {
        $config = Kohana::config('ecmproject');
        $email = Email::factory($config['registration_subject']);
        $email->from($config['outgoing_email_address'], $config['outgoing_email_name']);

        $regs = ORM::Factory('Registration')
            ->where('registrations.id', '=', $id)
            ->with('convention')
            ->where('email', '!=', '')
            ->order_by('email')
            ->find_all();

        $email = '';
        $data = array();
        $emails = array();
        foreach ($regs as $reg)
        {
            if ($email != $reg->email)
            {
                if (count($data))
                {
                    $this->email_imported_regs($email, $data);
                    array_push($emails, $email);
                }
                $data = array();
            }
            $email = $reg->email;
            $data['registrations'][$reg->convention->name][] = $reg;
            $data['name'] = $reg->gname . ' ' . $reg->sname;
        }
        if (count($data))
        {
            $this->email_imported_regs($email, $data);
            array_push($emails, $email);
        }

        $this->template->title = "Administration: Manage Accounts";
        $this->template->heading = "Administration: Manage Accounts";
        $this->template->subheading = "Resend out emails for registrations";

        $this->template->content = "Email successfully sent to to " . implode(', ', $emails);
    }

    function action_deleteRegistration($id = NULL) {
        Controller_Admin::__delete($id, 'Registration', 'deleteRegistration', 'manageRegistrations');
    }

    /*
    * Account CRUD actions
    */
    function action_manageAccounts() {
        // Set headers
        $this->template->title = "Administration: Manage Accounts";
        $this->template->heading = "Administration: Manage Accounts";
        $this->template->subheading = "Create, edit and delete accounts";

        $this->genericManageEntity('Account', 'Accounts');
    }
    function action_createAccount() {
        $this->template->title =        __('Admin: Create an Account');
        $this->template->heading =      __('Admin: Create an Account');
        $this->template->subheading =   __('Create an Account');

        $acct = ORM::factory('Account');
        $fields = $acct->default_fields;
        $fields['status']['values'] = Model_Account::getVerifySelectList();

        if ($post = $this->request->post())
        {
            $extra_validation = Validation::Factory($post);
            $extra_validation->rule('password', 'matches', array(':validation', 'password', 'confirm_password'));
            $acct->values($post);
            try {
                $acct->save($extra_validation);
                $this->addMessage( __('Created a new account, ') . $acct->email);
                $this->requireVerification($acct); // Require verification if status UNVERIFIED.
                $this->request->redirect('admin/manageAccounts');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
        }
        else {
            $post = $acct->as_array();
        }

        $this->template->content = new View('admin/Account', array(
            'row' => $acct->as_array(),
            'fields' => $fields,
            'callback' => 'createAccount'
        ));
    }
    function action_editAccount($id = NULL) {
        /* If no ID or bad ID defined, kill it with fire. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');

        $acct = ORM::factory('Account',$id);
        $fields = $acct->default_fields;
        $fields['status']['values'] = Model_Account::getVerifySelectList();

        /* If pass is not loaded, we have a problem */
        if (!$acct->loaded())
        {
            $errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';
            $this->request->redirect('admin/manageAccounts');
        }

        // Set headers
        $this->template->title =        __('Admin: Editing account, ') . $acct->email;
        $this->template->heading =      __('Admin: Editing account, ') . $acct->email;
        $this->template->subheading =   __('Edit the details of an account');

        if ($post = $this->request->post())
        {
            //Why not just add to ruleset of account...? Unset password fields for editing an account so validation rules don't trigger?
            $extra_validation = Validation::Factory($post);

            if ( $this->hasValue( $post, 'password' ) || $this->hasValue( $post, 'confirm_password' ) )
            {
                $extra_validation->rule('password', 'matches', array(':validation', 'password', 'confirm_password'));
            }
            else
            {
                unset($post['password']);
                unset($post['confirm_password']);
            }

            $acct->values($post);
            try {
                $acct->save($extra_validation);
                $this->addMessage('Successfully edited ' . $acct->email);
                $this->requireVerification($acct); // Require verification if status UNVERIFIED.
                $this->request->redirect('admin/manageAccounts');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
            }
        }
        else {
            $post = $acct->as_array();
        }

        //Parse UNIX timestamp back to something we can use.
        $this->template->content = new View('admin/Account', array(
            'row' => $acct->as_array(),
            'fields' => $fields,
            'callback' => "editAccount/$id"
        ));

    }
    function action_deleteAccount($id = NULL) {
        Controller_Admin::__delete($id, 'Account', 'deleteAccount', 'manageAccounts');
    }

    /*
    * Location CRUD actions
    */
    function action_manageLocations() {
        $this->template->title =        __('Admin: Locations');
        $this->template->heading =      __('Admin: Locations');
        $this->template->subheading =   __('Manage Ticket Sale Locations and their prefixes (used for registration ID generation)');

        $this->genericManageEntity('Location', 'Locations');
    }
    function action_createLocation() {
        $this->template->title =        __('Admin: Create a Location');
        $this->template->heading =      __('Admin: Create a Location');
        $this->template->subheading =   __('Create a new location');

        $loc = ORM::factory('Location');
        $fields = $loc->formo_defaults;
        $post = $loc->as_array();

        if ($post = $this->request->post())
        {
            $loc->values($post);
            try {
                $loc->save();
                $this->addMessage(__('Created a new location, ') . $loc->location);
                $this->request->redirect('admin/manageLocations');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong ... but it's not your fault. Contact the system maintainer please!");
            }
        }
        $this->template->content = new View('admin/Location', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => 'createLocation'
        ));
    }
    function action_editLocation($id = NULL) {
        /* TODO: Add a check to see if a location is in use. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');

        $loc = ORM::factory('Location', $id);
        $fields = $loc->formo_defaults;

        /* If pass is not loaded, we have a problem */
        if (!$loc->loaded())
        {
            $this->addError('Non-existent location! Maybe someone wiped it off the map when you weren\'t looking?');
            $this->request->redirect('admin/manageLocations');
        }

        $this->template->title =        __('Admin: Editing ') . $loc->location;
        $this->template->heading =      __('Admin: Editing ') . $loc->location;
        $this->template->subheading =   __('Edit the details of a location.');

        if ($post = $this->request->post())
        {
            $loc->values($post);
            try {
                $loc->save();
                $this->addMessage(__('Edited location, ') . $loc->location);
                $this->request->redirect('admin/manageLocations');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong ... but it's not your fault. Contact the system maintainer please!");
            }
        }
        else {
            $post = $loc->as_array();
        }

        $this->template->content = new View('admin/Location', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => "editLocation/$id"
        ));
    }
    function action_deleteLocation($id = NULL) {
        Controller_Admin::__delete($id, 'Location', 'deleteLocation', 'manageLocations');
    }

    /*
    * Admin actions
    */
    function action_manageAdmin() {
        $this->requirePermission('superAdmin'); //Require extra permissions to manage administrators.

        // Set headers
        $this->template->title =        __('Admin: Manage Admins');
        $this->template->heading =      __('Admin: Manage Admins');
        $this->template->subheading =   __('Manage the list of system administrators.');

        // Calculate the offset.
        //$start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );
        $rows = ORM::factory('usergroup', Controller_Admin::ADMIN_USERGROUP)->Accounts->find_all();

        // Header entry. (View with no data generates a header)
        $data['entries'][0] = new View('admin/ListItems/AdminAccountEntry');
        foreach ($rows as $row)
        {
            $data['actions']['delete'] = html::anchor(
                "/admin/deleteAdmin/". $row->id ,
                html::image(url::site('/static/img/edit-delete.png', TRUE), array('title'=>__("Remove Admin Priviledges"))),
                null, null, true
            );
            $data['entries'][$row->id] = new View('admin/ListItems/AdminAccountEntry', array('row' => $row, 'actions' => $data['actions']));
        }

        // Set callback path for form submit (change convention, jump to page)
        $this->template->content = new View('admin/list', array(
                'entity' => 'Account',
                'callback' => 'admin/manageAccounts',
                'createText' => 'Add Admin',
                'createLink' => 'admin/setAdmin',
                'rows' => $data['entries'],
                'page' => 1,
                'total_rows' => count($rows))
            );
    }
    function action_setAdmin() {
        $this->requirePermission('superAdmin'); //Require extra permissions to manage administrators.

        $fields = array('email' => array( 'type'  => 'text', 'label' => 'Email', 'required'=>true ));

        if ($post = $this->request->post())
        {
            $post['email'] = trim($post['email']);
            $group = ORM::Factory('usergroup', Controller_Admin::ADMIN_USERGROUP);
            $acct = ORM::Factory('Account')->where('email', '=', $post['email'])->find();
            if ($acct->loaded() && !$acct->has('Usergroups', $group))
            {
                $acct->add('Usergroups', $group);
                $acct->save();
                $this->addMessage('Account login ' . $acct->email . ' was granted administrator access.');
                $this->request->redirect('admin/manageAdmin');
            }
        }
        else
        {
            /* Full registration at this step. */
            $this->template->content = new View('admin/Admin', array(
                'row' => $reg['email'] = '',
                'fields' => $fields,
                'callback' => "setAdmin"
            ));
        }
    }
    function action_deleteAdmin($id = NULL) {
        $this->requirePermission('superAdmin'); //Require extra permissions to manage administrators.

        if ($id == NULL || !is_numeric($id))
            die('Get out of here!');

        $acct = ORM::Factory('Account', $id);
        $group = ORM::Factory('usergroup', Controller_Admin::ADMIN_USERGROUP);
        if ($acct->loaded() && $acct->has('Usergroups', $group))
        {
            $acct->remove('Usergroups', $group);
            $acct->save();
            $this->addMessage('Account login ' . $acct->email . ' was stripped of admin access.');
        }
        else
        {
            $this->addError("Not a valid (admin) account.");
        }

        $this->request->redirect('admin/manageAdmin');
    }

    /*
    * Search and other actions
    */
    function action_search($entity = NULL) {
        $this->template->subheading = __('Displaying search results');

        //Determine search term (POST).
        $post = $this->request->post();

        if (isset($post['search_term']))
            $search_term = '%' . $post['search_term'] . '%';
        else
            $search_term = null;

        //Go context sensitive...
        $rows = null;
        if ($entity == 'Registration' && $search_term != null)
        {
            $this->template->heading = __('Searching for Registrations');
            if (preg_match('/^\w+-\d{2}-\d+$/', $post['search_term']) === 1)
            {
                list($location_name, $convention_id, $reg_id) = explode('-', $post['search_term']);
                $rows = ORM::Factory('Registration')
                    ->with('location')
                    ->where('location.prefix','=', $location_name)
                    ->where('convention_id','=', $convention_id)
                    ->where('reg_id','=', $reg_id)
                    ->find_all();
            }
            else
            {
                $rows = ORM::Factory('Registration')
                    ->or_where('email', 'LIKE', $search_term)
                    ->or_where('gname', 'LIKE', $search_term)
                    ->or_where('sname', 'LIKE', $search_term)
                    ->or_where('reg_id', 'LIKE', $search_term)
                    ->find_all();
            }
        }
        else if ($entity == 'Account' && $search_term != null)
        {
            $this->template->heading = __('Searching for Accounts');
            $rows = ORM::Factory('Account')
                ->or_where('email', 'LIKE', $search_term)
                ->or_where('id', '=', $search_term)
                ->find_all();
        }
        else if ($entity == 'Convention' && $search_term != null)
        {
            $this->template->heading = __('Searching for Events');
            $rows = ORM::Factory('Convention')
                ->or_where('name', 'LIKE', $search_term)
                ->or_where('location', 'LIKE', $search_term)
                ->find_all();
        }
        else if ($entity == 'Pass' && $search_term != null)
        {
            $this->template->heading = __('Searching for Tickets');
            $rows = ORM::Factory('Pass')
                ->or_where('name', 'LIKE', $search_term)
                ->find_all();
        }
        else if ($entity == 'Location' && $search_term != null)
        {
            $this->template->heading = __('Searching for Locations');
            $rows = ORM::Factory('Location')
                ->or_where('prefix', '=', $search_term)
                ->or_where('location', 'LIKE', $search_term)
                ->find_all();
        }

        // Header entry. (View with no data generates a header)
        if ($rows != null)
        {
            $data['entries'][0] = new View("admin/ListItems/$entity" . 'Entry');
            foreach ($rows as $row)
            {
                $data['actions']['edit'] = html::anchor(
                    "admin/edit$entity/". $row->id,
                    html::image(url::site('/static/img/edit-copy.png', TRUE), array('title'=>__("Edit $entity"))),
                    null, null, true
                );
                $data['actions']['delete'] = html::anchor(
                    "admin/delete$entity/" . $row->id,
                    html::image(url::site('/static/img/edit-delete.png',TRUE), array('title'=>__("Delete $entity"))),
                    null, null, true
                );

                $data['entries'][$row->id] = new View("admin/ListItems/$entity" . 'Entry', array('row' => $row, 'actions' => $data['actions']));
            }
        } else if ($search_term == null) {
            $this->addError('No search term entered.');
            $data['entries'] = null;
        } else {
            $this->addError('Searching for something that does not exist!');
            $data['entries'] = null;
        }

        // Set callback path for form submit (change convention, jump to page)
        $this->template->content = new View('admin/Search', array(
                'callback' => "admin/search/$entity",
                'rows' => $data['entries'],
        ));
    }
    /*
    * Export is limited to exporting registration information. We will need to know the convention to export for and any conditions the
    * exporter wants applied. We will do this in two stages. One to determine the convention, and the other to determine the remaining
    * conditions to be applied. (GO checkboxes).
    *
    * Conditions:
    *   * Include those with a certain age or greater (or less).
    *   * Export by pass type.
    *   * Export by registration status.
    */
    public function action_export() {
        // Set headers
        $this->template->title = "Administration: Export";
        $this->template->heading = "Administration: Export";
        $this->template->subheading = "Export Registration Info to CSV";

        $reg = ORM::factory('Registration');
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');

        $fields = $reg->formo_defaults;
        $fields['convention_id'] = array( 'type'  => 'select', 'label' => 'Convention', 'required'=>true );
        $fields['convention_id']['values'] = $crows;

        if ($post = $this->request->post())
        {
            //Validate valid convention id, validate account - create if not exist email. Then pass it on as a POST.
            if (Model_Convention::validConvention($post['convention_id']))
            {
                $this->request->redirect("admin/export2/" . $post['convention_id']); //Move to next STEP
            }

            $this->addError("One or more fields are blank!");   //pass_field_reg_error_convention_id_missing
            $this->template->content = new View('admin/Export', array(
                'row' => $post,
                'fields' => $fields,
                'callback' => 'export'
            ));
        }
        else
        {
            $this->template->content = new View('admin/Export', array(
                'row' => $reg->as_array(),
                'fields' => $fields,
                'callback' => 'export'
            ));
        }
    }
    public function action_export2($cid) {
        if (!isset($cid) || !is_numeric($cid) || $cid <= 0)
            die('Get out of here!');

        $passes = ORM::Factory('Pass')->where("convention_id", '=', $cid)->find_all();
        $status_values = Model_Registration::getStatusValues();

        if ($post = $this->request->post())
        {
            $export_passes = array();
            $export_status = array();
            $export_age = array();

            //Determine what to export. This is a bit cheap but it works...
            foreach($post as $k => $v):

                /* Pass */
                if ($k[0] == 'p')
                {
                    $temp = explode("_", $k);
                    $export_passes[$temp[1]] = $temp[1];
                }

                /* Status */
                else if ($k[0] == 's')
                {
                    $temp = explode("_", $k);
                    $export_status[$temp[1]] = $temp[1];
                }

            endforeach;

            $this->doExport($cid, $export_passes, $export_status);
        }

        $this->template->content = new View('admin/Export2', array(
                'passes' => $passes,
                'status_values' => $status_values,
                'callback' => "export2/$cid"
            ));
    }


    /*
    * Private generic/utility functions.
    */
    private function __delete($id, $entityType, $callback, $return, $updatePaymentStatus = NULL)  {
        /* If no ID or bad ID defined, kill it with fire. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');

        $row = ORM::factory($entityType,$id);

        if (isset($row->name))
            $entityName = $row->name;
        else if (isset($row->email))
            $entityName = $row->email;
        else if (isset($row->location))
            $entityName = $row->location;
        else if (isset($row->type))
            $entityName = $row->type;
        else if (isset($row->reg_id))
            $entityName = $row->reg_id; //hack.
        else
            $entityName = 'Unknown';

        /* If row is defined (only if ID was set) and row was loaded... */
        if ($row->loaded())
        {
            /* POST value YES ... do delete */
            if ($val = $this->request->post('Yes'))
            {
                if ($updatePaymentStatus)
                {
                        //We need to fetch reg, pass, and payment objects...
                        $pay = ORM::Factory('Payment',$id);
                        $reg = ORM::Factory('Registration',$pay->register_id);
                        $pass = ORM::Factory('Pass',$reg->pass_id);
                }

                if ($row->delete())
                {
                    $this->addMessage($entityName . " was deleted. D:");

                    if ($updatePaymentStatus)
                    {
                        $this->updatePaymentStatus($reg, $pass);
                    }

                    $this->request->redirect("admin/$return");
                }
                else
                {
                    $this->addError("Failed to delete convention with ID: $id! Please try again.");
                    $this->request->redirect("admin/$return");
                }
            }
            /* User changed mind. */
            else if ($val = $this->request->post('No'))
            {
                $this->request->redirect("admin/$return");
            }

            $this->template->content = new View('admin/delete', array(
                'entityType' => $entityType,
                'entityName' => $entityName,
                'callback' => $callback,
                'id' => $id
            ));
        }
        else {
            $this->addError("Loading error: $id $entityType $callback $return");
            $this->request->redirect("admin/$return");
        }
    }

    /*
    * Validate and determine the convention id to use. If $convention_id is
    * NULL, determine it from a result set of convention entries.
    */
    private function getConventionId($convention_id, $crows) {
        /* POST Variable defines $convention_id */
        if ($convention_id == NULL)
        {
            $cid = $this->request->post('convention_id');

            if (isset($cid) && is_numeric($cid))
                return $cid;
            else if (isset($cid))
                $this->addError("Attempted to view an invalid convention, reverting to default.");

        }

        /* GET Variable defines $convention_id */
        else if ($convention_id != NULL)
        {
            if (is_numeric($convention_id))
                return $convention_id;
            else
                $this->addError("Attempted to view an invalid convention, reverting to default.");
        }

        /* No valid GET/POST defined if it makes it this far. */
        if ($id = key($crows))
            return $id;
        else
            return -1;
    }

    /*
    * Validate and determine the page multiplier to use when fetching results from the DB.
    */
    private function getMultiplier($page) {
        // Page variable is a number.
        if (isset($page) && is_numeric($page))
        {
            $multiplier = $page - 1; // Subtract one since we're working starting from zero.
        }
        // Non-set or non-valid multiplier.
        else if (!isset($multiplier) || !is_numeric($multiplier))
        {
            $multiplier = 0;
        }

        return $multiplier;
    }

    /*
    * doExport - Export all information pertaining to the registration and pass cost.
    *
    * $cid is the Convention identifier that we will export the list of registrations from.
    * $passes is the array of allowed pass types for registrations to be included in the exported list.
    * $status is the array of allowed status values for registrations exported to the list.
    *
    * $age is a single value of either "all", "minor", "adult", or "none".
    */
    private function doExport($cid, $passes, $status) {
        if ($cid == null || !is_numeric($cid)) {
            die('Get out of here!');
        }

        $query = ORM::Factory('Registration');
        if ($passes != null && is_array($passes) && count($passes) > 0)
        {
            $query = $query->where('registrations.pass_id', 'IN', $passes);
        }

        if ($status != null && is_array($status) && count($status) > 0)
        {
            $query = $query->where('registrations.status', 'IN', $status);
        }

        //Lazy vs eager? We're going to use it all...get it all in one go.
        $results = $query->where("registrations.convention_id", '=', $cid)->with('pass')->with('account')->with('convention')->find_all();

        /* Generate a header */
        $csv_content = "Ticket Number,Name,Email,Phone Number,Date of Birth,Event,Ticket,Payment Status,Pickup Status\n"; //FIXME: Have it use values/labels in the Reg model instead.

        /* Generate the content */
        $convention_name = '';
        $pass_names = array();

        foreach($results as $result) {
            /* Cache pass names */
            if ( !array_key_exists($result->pass_id, $pass_names) ) {
                $pass_names[$result->pass_id] = $result->pass->name;
            }

            $temp = array(
                    $result->reg_id,
                    $result->gname . ' ' . $result->sname,
                    $result->email,
                    $result->phone,
                    $result->dob,
                    empty($convention_name) ? $result->convention->name : $convention_name,
                    $pass_names[$result->pass_id],
                    $result->statusToString(),
                    $result->pickupToString(),
            );

            /* In case of commas in field values ... */
            foreach($temp as $value) {
                $value = "\"" . $value . "\"";
            }

            $csv_content .= implode(',', $temp) . "\n";
        }

        $filename = 'Registrations_' . date("n_d_Y_G_H") . '.csv';
        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Expires: 0");

        print $csv_content;
        exit;
    }



    /*
    * Generic data listing method. Generates a list view with create/edit/delete actions based on the $entity name
    * and a page number.
    *
    * Notes: The $entity name must match the name of the Model and the action must be named accordingly: action_manage$entity
    *        Any custom functionality is not supported currently - you must implement your own manage controller code.
    *
    * TODO: Add callback support to override default logic (page data, row processing, required information).
    *
    * string $entity
    * string $plural_entity
    * int $page
    * array opt_conditions = NULL
    * array opt_viewAttributes = NULL
    *
    * returns null
    */
    private function genericManageEntity($entity, $plural_entity, $opt_conditions = NULL, $opt_viewAttributes = NULL) {

        $page = $this->request->query('page', null);

        //Get total number of rows. Restrict as necessary.
        $total_rows = ORM::Factory($entity);
        if( $opt_conditions ) {
            foreach ($opt_conditions as $field => $value) {
                $total_rows->where($field, '=', $value);
            }
        }
        $total_rows = $total_rows->count_all();

        //Get the page data. Errors automatically generated in the event of an empty result.
        if ( $rows = $this->getPageData($total_rows, $entity, $page, $opt_conditions) )
        {
            $data = $this->generateViewRows($rows, $entity);
        }
        //Invalid page number. Set required variables.
        else
        {
            $data = array();
            $data['entries'] = array();
        }

        $view_attributes = array(
            'entity' => $entity,
            'callback' => "admin/manage$plural_entity",
            'createText' => __("Create new $entity"),
            'createLink' => url::site("admin/create$entity", TRUE),
            'rows' => $data['entries'],
            'page' => $page,
            'total_rows' => $total_rows
        );

        if ($opt_viewAttributes) {
            $view_attributes = array_merge($view_attributes, $opt_viewAttributes);
        }

        $this->template->content = new View('admin/list', $view_attributes);
    }

    /*
    * Given an entity, calculate the current page and fetch the entity data associated with that page.
    * Number of data rows retrieved is defined by the Controller_Admin::ROWS_PER_PAGE constant.
    *
    * An array providing additional where conditions can be provided.
    *
    * int $total_rows
    * string $entity
    * int $page
    * array opt_conditions
    * returns Database_Result array()
    * @ref http://kohanaframework.org/3.1/guide/api/ORM#find_all
    */
    private function getPageData($total_rows, $entity, $page, $opt_conditions = NULL) {
        //If $page is not a number, getMultiplier will return 0.
        $start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );
        $rows = ORM::factory($entity)->limit(Controller_Admin::ROWS_PER_PAGE)->offset($start);

        if ($opt_conditions) {
            foreach($opt_conditions as $field => $value) {
                $rows->where($field, '=', $value);
            }
        }

        $rows = $rows->find_all();

        //Error conditions.
        if (count($rows) == 0 && $total_rows > 0)
        {
            $this->addError( __('Invalid page number') );
        }
        else if(!count($rows))
        {
            $this->addError( __("You should probably create an $entity to start with.") );
        }

        return $rows;
    }

    /*
    * Generic row generation method which adds two actions (edit and delete) per row.
    *
    * $rows - The database results to be used in generating rows.
    * $entity - The entity matching those rows.
    */
    private function generateViewRows($rows, $entity) {
        //'admin/ListItems/ConventionEntry'
        $data['entries'][0] = new View("admin/ListItems/$entity" . 'Entry');
        foreach ($rows as $row)
        {
            $data['actions']['edit'] = html::anchor(
                "/admin/edit$entity/". $row->id ,
                html::image(url::site('/static/img/edit-copy.png', TRUE), array('title'=>__("Edit $entity"))),
                null, null, true
            );
            $data['actions']['delete'] = html::anchor(
                "/admin/delete$entity/" . $row->id,
                html::image(url::site('/static/img/edit-delete.png',TRUE), array('title'=>__("Delete $entity"))),
                null, null, true
            );
            $data['entries'][$row->id] = new View(
                "admin/ListItems/$entity" . 'Entry',
                array('row' => $row, 'actions' => $data['actions'])
            );
        }

        return $data;
    }

    private function requireVerification($account) {
        if (!$account->isVerified()) {
            $vcode = $account->generateVerifyCode(Model_Verificationcode::TYPE_VALIDATE_EMAIL);
            $account->sendValidateEmail($vcode->original_code);
        }
    }

    private function parseErrorMessages($e, $extra_e = NULL) {
        $errorMsg = 'Oops. You entered something bad! Please fix it! <br />';
        $errors = $e->errors(''); //Loads from directory specified by argument here.

        //Add standard (ORM usually) errors.
        foreach ($errors as $error) {
            if ( is_array($error) ) {
                foreach($error as $inline_error) {
                    $errorMsg = $errorMsg . ' ' . $inline_error . '<br />';
                }
            }
            else {
                $errorMsg = $errorMsg . ' ' . $error . '<br />';
            }
        }

        //Add extra errors from an external source.
        if ( is_array($extra_e) && $extra_e ) {
            foreach ($extra_e as $error) {
                $errorMsg = $errorMsg . ' ' . $error . '<br />';
            }
        }

        //Display errors.
        $this->addError($errorMsg);
    }

    private function hasValue($model, $field) {
        if (!isset($model[$field])) {
            return false;
        }

        $value = trim($model[$field]);
        return !( empty($value) );
    }
    private function validateEmailOrPhone($post) {
        $extra_validation = Validation::Factory($post);

        if (empty($post['phone'])) {
            $extra_validation->rule('email', 'not_empty');
        }
        else if (empty($post['email'])) {
            $extra_validation->rule('phone', 'not_empty');
        }

        return $extra_validation;
    }
    public function action_import() {
        $pass_id = -1;
        $import_success = array(); //Store reg_id's that were successfully imported.
        $import_failure = array(); //Store reg_id's that were not imported (error).
        $reg_ids = array();

        if ( $post = $this->request->post() ) {

            $validate = new Validation($_FILES);
            $validate->rules('csv_file', array(
                    array('Upload::not_empty', NULL),
                    array('Upload::valid' , NULL),
                    array('Upload::size', array(':value','1M')),
                    array('Upload::type' , array(':value', array('csv') //Only checks by extension.
                    )),
            ));

            $pass_id = empty($post['pass_id']) ? -1 : $post['pass_id'];
            $pass = $this->loadPass($post);

            if ( $validate->check() && isset($_FILES['csv_file']) && $pass )
            {
                $handle = fopen($_FILES['csv_file']['tmp_name'], 'rb');
                $line = fgetcsv($handle);

                while ($line = fgetcsv($handle))
                {
                    $line[0] = trim($line[0]); //In the event of a blank row, there is only one element.
                    if (empty($line[0])) {
                        continue;
                    }
                    /* FIXME - select * from location
                     * if location doesn't match do an insert and update cache hash
                     */

                    $reg = $this->generateReg($pass, $line);
                    if (!$reg->pickupStatus) { $reg->pickupStatus = 0; }
                    $values = array('email' => $reg->email, 'phone' => $reg->phone);

                    try {
                        $reg->save( $this->validateEmailOrPhone($values) );
                        if ( !isset($import_success[$reg->email]) ) {
                            $import_success[$reg->email] = array();
                        }
                        array_push($import_success[$reg->email], $reg);
                        if (@$_POST['email_on_completion'])
                            array_push($reg_ids, $reg->id);
                    }
                    catch (ORM_Validation_Exception $e)
                    {
                        array_push($import_failure, array('reg' => $reg, 'errors' => $e->errors('')));
                    }
                }

                fclose($handle);
                /* FIXME: Send an email per index. */
            }
            else if (!$pass) {
                $this->addError('Not a valid ticket! Please select a valid ticket.');
            }
            else
            {
                $this->addError('Not a valid file. Please try again.');
            }
        }

        if ( !empty($import_success) || !empty($import_failure) )
        {
            $this->template->content = new View('admin/UploadResults', array(
                'import_success' => $import_success,
                'import_failure' => $import_failure,
            ));

            if (!empty($reg_ids) && @$_POST['email_on_completion'])
            {
                $config = Kohana::config('ecmproject');
                $email = Email::factory($config['registration_subject']);
                $email->from($config['outgoing_email_address'], $config['outgoing_email_name']);

                $regs = ORM::Factory('Registration')
                    ->where('registrations.id', 'IN', $reg_ids)
                    ->with('convention')
                    ->where('email', '!=', '')
                    ->order_by('email')
                    ->find_all();

                $email = '';
                $data = array();
                foreach ($regs as $reg)
                {
                    if ($email != $reg->email)
                    {
                        if (count($data))
                        {
                            $this->email_imported_regs($email, $data);
                        }
                        $data = array();
                    }
                    $email = $reg->email;
                    $data['registrations'][$reg->convention->name][] = $reg;
                    $data['name'] = $reg->gname . ' ' . $reg->sname;
                }
                if (count($data))
                {
                    $this->email_imported_regs($email, $data);
                }
            }
        }
        else {
            $passes = ORM::Factory('pass')
                ->with('convention')
                ->find_all();

            $this->template->content = new View('admin/Upload', array(
                'passes'         => $passes,
                'pass_id'        => $pass_id,
            ));
        }
    }

    private function email_imported_regs($addr, $data)
    {
        $template_suffix = "";
        if ($data['registrations'])
        {
            $registrations = array_values($data['registrations']);
            $convention_id = $registrations[0][0]->convention->id;
            list($prefix, $convention, $id) = explode("-", $registrations[0][0]->reg_id);
            if ($prefix)
                $template_suffix = "_$prefix";
        }

        /** FIXME - this really needs to be centralized and not hacked so ugly in
         * Order should be:
         * reg_success_ART--3.php
         * reg_success--3.php
         * reg_success_ART.php
         * reg_success.php
         */
        try {
            $view = new View('convention/reg_success'.$template_suffix.'--'.$convention_id, $data);
            Kohana::$log->add(Log::NOTICE,"[email_imported_regs] 'convention/reg_success'.$template_suffix.'--'.$convention_id was found");
        }
        catch (Kohana_View_Exception $e)
        {
            try {
                $view = new View('convention/reg_success--'.$convention_id, $data);
                Kohana::$log->add(Log::NOTICE,"[email_imported_regs] 'convention/reg_success--'.$convention_id was found");
            }
            catch (Kohana_View_Exception $e)
            {
                try {
                    $view = new View('convention/reg_success'.$template_suffix, $data);
                    Kohana::$log->add(Log::NOTICE,"[email_imported_regs] 'convention/reg_success'.$template_suffix was found");
                }
                catch (Kohana_View_Exception $e)
                {
                    $view = new View('convention/reg_success', $data);
                    Kohana::$log->add(Log::NOTICE,"[email_imported_regs] 'convention/reg_success' was found");
                }
            }
        }

        $msg = $view->render();

        $config = Kohana::config('ecmproject');
        $email = Email::factory($config['registration_subject']);
        $email->from($config['outgoing_email_address'], $config['outgoing_email_name']);
        $email->message($msg,'text/html');
        $email->to($addr);
        $email->send();
    }
    private function generateReg($pass, $line) {
        $reg = ORM::Factory('Registration');
        $reg->reg_id  = $line[0];
        $reg->convention_id = $pass->convention_id;
        $reg->pass_id       = $pass->id;
        $reg->status        = Model_Registration::STATUS_PAID;

        /* If this block is not executed, validation will fail on save() */
        if (count($line) >= 6) {
            $name_components = explode(' ', $line[1]);
            $gname_components = array_slice($name_components, 0, count($name_components) - 1);
            $gname = implode(' ', $gname_components);
            $sname = $name_components [ count($name_components) - 1 ];

            $reg->reg_id        = $line[0];
            $reg->gname         = $gname;
            $reg->sname         = $sname;
            $reg->email         = $line[2];
            $reg->phone         = $line[3];

            if ( !empty($line[4]) )
            {
                $date = str_replace("/", "-", $line[4]);
                $date = date("Y-m-d", strtotime($date));

                if ($date) {
                    $reg->dob = $date;
                }
            }
        }

        return $reg;
    }
    private function loadPass($post) {
        if ( !empty($post['pass_id']) ) {
            $pass = ORM::Factory('Pass', $post['pass_id']);
            if ( $pass->loaded() ) {
                return $pass;
            }
        }
        return false;
    }

    public function action_testView() {
        $this->template->content = new View('admin/UploadResults');
    }
    public function action_regID() {
        header("Content-type: text/plain");
        print sprintf('%s-%02s-%04s', 'ECM', 1, 5);
        exit;
    }
    public function action_testClock() {
        header("Content-type: text/plain");
        print date("r");
        exit;
    }
}
