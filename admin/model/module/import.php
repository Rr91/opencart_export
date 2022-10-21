<?php
class ModelModuleimport extends Model {
	public function findProduct($sku) {
		$result = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE sku = '" . $sku . "'");

		foreach ($query->rows as $row) {
			$result[] = array(
				'id'   => $row['product_id'],
				'name' => $row['model'],
			);
		}

		return $result;
	}

	public function updateProduct($data) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET price=".$data['price'].", quantity=".$data['quantity']." WHERE product_id = " . $data['product_id'] . "");
		// $query = $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = ". $data['product_id']);

		$query = $this->db->query("SELECT cg.customer_group_id as id from " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd on (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.name IN ('Первая покупка', 'Пятая покупка')");
		$g1_id = $query->rows[0]['id'];
		$g2_id = $query->rows[1]['id'];

		$query = $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = ".$data['product_id']." AND customer_group_id = ".$g1_id);
		$query = $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = ".$data['product_id']." AND customer_group_id = ".$g2_id);
		$query = $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount (product_id, customer_group_id, price) VALUES(".$data['product_id'].", ".$g1_id.", ".$data['disc1'].") ");
		$query = $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount (product_id, customer_group_id, price) VALUES(".$data['product_id'].", ".$g2_id.", ".$data['disc2'].") ");
	}

	public function getExportData() {
		$query = $this->db->query("SELECT cg.customer_group_id as id from " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd on (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.name IN ('Первая покупка', 'Пятая покупка')");
		$g1_id = $query->rows[0]['id'];
		$g2_id = $query->rows[1]['id'];

		return $query = $this->db->query("SELECT sku, quantity, price, (SELECT price FROM ".DB_PREFIX."product_discount pd1 WHERE pd1.product_id = p.product_id and customer_group_id = {$g1_id} limit 1) as disc1, (SELECT price FROM ".DB_PREFIX."product_discount pd2 WHERE pd2.product_id = p.product_id and customer_group_id = {$g2_id} limit 1) as disc2 FROM ".DB_PREFIX."product p where sku <> ''")->rows;
	}
}