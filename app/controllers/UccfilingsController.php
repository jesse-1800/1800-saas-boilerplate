<?php

class UccfilingsController
{
    public function paginate($partner_id)
    {
        $database   = new Kernel\Database\Database();
        $curr_page  = (int)($_POST['curr_page'] ?? 1);
        $page_size  = (int)($_POST['page_size'] ?? 100);
        $offset     = ($curr_page - 1) * $page_size;
        $sort_map   = array(
            'id'              => 'UF.id',
            'ucc_date'        => "STR_TO_DATE(UF.ucc_date,'%m/%d/%Y')",
            'ucc_status'      => 'UF.ucc_status',
            'buyer_company'   => 'buyer_company',
            'buyer_state'     => 'buyer_state',
            'equipment_count' => 'equipment_count',
        );
        $sort_by    = $sort_map[$_POST['sort_by']] ?? 'id';
        $order_by   = $_POST['order_by'];
        $total      = $database->row("SELECT COUNT(*) AS 'total' FROM ucc_filings WHERE partner_id='$partner_id'")->total;

        // where queries
        $where         = array();
        $search        = trim($_POST['search']        ?? '');
        $start_date    = trim($_POST['start_date']    ?? '');
        $end_date      = trim($_POST['end_date']      ?? '');
        $provider_id   = trim($_POST['provider_id']   ?? '');
        $assignee_id   = trim($_POST['assignee_id']   ?? '');
        $ucc_status    = trim($_POST['ucc_status']    ?? '');
        $buyer_state   = trim($_POST['buyer_state']   ?? '');
        $equipment_min = trim($_POST['equipment_min'] ?? '');
        $equipment_max = trim($_POST['equipment_max'] ?? '');

        if ($partner_id !== "") {
            $where[] = "UF.partner_id = '$partner_id'";
        }
        if ($search !== ""){
            $search_input = addslashes($search);
            $where[] = ("
                (buyer_company LIKE '%$search_input%'
                OR buyer_state LIKE '%$search_input%'
                OR UF.ucc_status LIKE '%$search_input%'
                OR UF.id LIKE '%$search_input%')
            ");
        }
        if ($start_date !== "" && $end_date !== ""){
            $where[] = ("
                STR_TO_DATE(UF.ucc_date,'%m/%d/%Y')
                BETWEEN STR_TO_DATE('$start_date','%m/%d/%Y')
                AND STR_TO_DATE('$end_date','%m/%d/%Y')
            ");
        }
        if ($provider_id !== ""){
            $where[] = "UF.provider_id = '$provider_id'";
        }
        if ($assignee_id !== ""){
            $where[] = "UF.assignee_id = '$assignee_id'";
        }
        if ($ucc_status !== ""){
            $where[] = "UF.ucc_status = '$ucc_status'";
        }
        if ($buyer_state !== ""){
            $where[] = "UB.buyer_state = '$buyer_state'";
        }
        if ($equipment_min !== '' && $equipment_max !== ''){
            $where[] = "(
                SELECT COUNT(*)
                FROM ucc_equipments EQ
                WHERE EQ.ucc_filing_id = UF.id
            ) BETWEEN $equipment_min AND $equipment_max";
        }

        $where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
        $equipment_count = "(SELECT COUNT(*) FROM ucc_equipments EQ WHERE EQ.ucc_filing_id = UF.id)";
        $result = $database->query("
          SELECT UF.*,UB.buyer_company,UB.buyer_state,$equipment_count AS equipment_count
          FROM ucc_filings UF
          LEFT JOIN ucc_buyers UB ON UF.buyer_id = UB.id
          $where_sql
          ORDER BY $sort_by $order_by
          LIMIT $page_size
          OFFSET $offset;
        ");

        return json(['items' => $result, 'total' => $total]);
    }
}