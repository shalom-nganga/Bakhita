<?php $version = $this->db->get_where('settings', array('type' => 'version'))->row()->description;?>
<!-- Footer -->
<footer class="main">
	&copy; 2024 <strong>St BAKHITA HEALTH FACILITY  Management System</strong>
    <strong class="pull-right"> VERSION <?php echo $version;?></strong>
    Developed by M.C & S.N
</footer>
