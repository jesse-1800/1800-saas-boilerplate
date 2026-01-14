<?php

use App\Models\UccContacts;
use Kernel\Database\Database;

class ContactsController {
    public function paginate($partner_id)
    {
        $database  = new Database();
        $curr_page = (int)($_POST['curr_page'] ?? 1);
        $page_size = (int)($_POST['page_size'] ?? 100);
        $offset    = ($curr_page - 1) * $page_size;
        $sort_map  = array(
            'id'      => 'CT.id',
            'fullname'=> 'fullname',
            'company' => 'UB.buyer_company',
            'title'   => 'CT.title',
            'email'   => 'CT.email',
        );
        $sort_by   = $sort_map[$_POST['sort_by']] ?? 'id';
        $order_by  = $_POST['order_by'];
        $total     = $database->row("SELECT COUNT(*) AS 'total' FROM ucc_contacts WHERE partner_id='$partner_id'")->total;

        // where queries
        $where  = array();
        $search = trim($_POST['search'] ?? '');

        if ($partner_id !== "") {
            $where[] = "CT.partner_id = '$partner_id'";
        }
        if ($search !== "") {
            $search_input = addslashes($search);
            $where[] = ("
                (
                    CT.id LIKE '%$search_input%' OR
                    CONCAT(CT.firstname,' ',CT.lastname) LIKE '%$search_input%' OR
                    UB.buyer_company LIKE '%$search_input%' OR
                    CT.title LIKE '%$search_input%' OR
                    CT.email LIKE '%$search_input%'
                )
            ");
        }

        $where_sql = count($where) ? "WHERE ".implode(" AND ", $where) : "";
        $result = $database->query("
          SELECT CT.*,CONCAT(CT.firstname,' ',CT.lastname) 
          AS fullname,UB.buyer_company,UB.id AS buyer_id
          FROM ucc_contacts CT
          LEFT JOIN ucc_buyers UB ON CT.buyer_id = UB.id
          $where_sql
          ORDER BY $sort_by $order_by
          LIMIT $page_size
          OFFSET $offset;
        ");

        return json(['items' => $result, 'total' => $total]);
    }

    public function store()
    {
        $contact = json_decode($_POST['contact']);
        unset($contact->id);
        unset($contact->created_at);
        unset($contact->updated_at);
        $result = UccContacts::insert($contact);
        return json([
            'result' => $result,
            'message' => $result ? 'Contact has been added!':"An error occurred, can't add a contact."
        ]);
    }

    public function update()
    {
        $contact = json_decode($_POST['contact']);
        $result = UccContacts::update($contact,$contact->id);
        return json([
            'result' => $result,
            'message' => $result ? 'Contact has been updated!':"An error occurred, can't update contact."
        ]);
    }
}