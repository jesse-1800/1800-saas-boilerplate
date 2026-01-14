<?php

use App\Models\IDProviderPartnerUsers;
use App\Models\PartnerUsers;

/**
 * Default controller
 * Class HomeController
 */
class UsersController
{
    /**
     * Fetch all users
     */
    public function fetch()
    {
        $auth = new Auth();
        return $auth->fetch_users();
    }

    /**
     * Create a new user
     */
    public function store()
    {
        $auth = new Auth();
        $auth->isLoggedIn();
        $form = (object) json_decode($_POST['form']);
        $form->partner_id = $_POST['partner_id'];
        return $auth->create_user($form);
    }

    /**
     * Delete a user
     */
    public function destroy($user_id)
    {
        $auth = new Auth();
        $auth->isLoggedIn();
        return json($auth->delete_user($user_id));
    }

    /**
     * Toggle block/unblock status
     */
    public function toggle_status()
    {
        $auth = new Auth;
        $auth->isLoggedIn();
        $user_id = $_POST['user_id'];
        $status = $_POST['block_value'] == 1;
        $result = $auth->set_user_blocked($user_id, $status);
        return json([
            'result' => isset($result['user_id']),
        ]);
    }

    /**
     * Fetch all available roles
     */
    public function fetch_roles()
    {
        return json(
            (new Auth)->fetch_roles()
        );
    }

    /**
     * Refetch latest USER INFO
     */
    public function user_info()
    {
        $auth = new Auth;
        $auth->isLoggedIn();
        return json(
            $auth->fetch_user_info(
                $_POST['user_id']
            )
        );
    }

    /**
     * Update user profile
     */
    public function update_profile()
    {
        $auth = new Auth();
        $auth->isLoggedIn();
        $profile = json_decode($_POST['profile']);

        // If image is uploaded, save it.
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $filename = $this->save_avatar($_POST['default_avatar']);
            $profile->picture = $filename;
        }
        $response = $auth->update_profile($profile);

        return json([
            'result'  => isset($response['user_id']),
            'profile' => $auth->fetch_user_info($response['user_id']),
            'response' => $response,
        ]);
    }

    /**
     * Private method to upload avatar locally
     * @param string $default_avatar
     * @return string URL of the uploaded avatar
     */
    private function save_avatar($default_avatar)
    {
        // Auth0 won't accept localhost URL so we're providing a
        // temporary image URL. Test this in LIVE instead.
        if (SYSTEM_MODE == 'development') {
            return "https://placehold.co/100x100/EEE/31343C?font=oswald&text=DEV";
        }

        if (empty($_FILES['avatar']['tmp_name']) || $_FILES['avatar']['error'] !== 0) {
            return ['result' => false, 'message' => 'No file uploaded'];
        }

        $path = "{$_SERVER['DOCUMENT_ROOT']}/storage/avatars/";
        $type = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $name = time().".$type";

        // Upload the file and return $filename
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $path.$name)) {
            return full_hostname()."/storage/avatars/$name";
        }

        // If shit fails, revert to orig avatar.
        return $default_avatar;
    }

    /**
     * Update the user's email signature.
     */
    public function update_email_signature()
    {
        $Model = new IDProviderPartnerUsers;
        $user_id = $_POST['user_id'];
        $partner_user = $Model::where('user_id',$user_id)->first();
        if ($partner_user) {
            $partner_user->email_signature = $_POST['email_signature'];
            return json([
                'result' => $partner_user->save(),
            ]);
        } else {
            return json([
                'result' => false,
                'message' => "We couldn't update your signature this time.",
            ]);
        }
    }


    /** * Change role */
    public function change_role()
    {
        (new Auth)->isLoggedIn();
        $partner_user = (new PartnerUsers)::where('user_id',$_POST['user_id'])->first();
        if ($partner_user) {
            $partner_user->role = $_POST['role'];
            $result = $partner_user->save();
            return json([
                'result' => $result,
                'message' => $result ? "Role updated successfully." : "An error occurred. Please try again.",
            ]);
        } else {
            return json([
                'result' => false,
                'message' => "We couldn't update your role this time.",
            ]);
        }
    }
}
