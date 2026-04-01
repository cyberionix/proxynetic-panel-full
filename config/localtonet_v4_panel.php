<?php return array (
  'http_port' => 8888,
  'socks_port' => 9999,
  'random_port_min' => 20000,
  'random_port_max' => 65000,
  /* Manuel atamada tercih: 5 haneli (10000–99999); hata olunca farklı port üretilir */
  'manual_assign_port_min' => 10000,
  'manual_assign_port_max' => 99999,
  /* Dış döngü (stop + rezerve sonrası); içte port_assign_inner_tries kadar farklı port */
  'port_assign_max_attempts' => 20,
  'port_assign_inner_tries' => 8,
  /* Stop etmeden önce v2 PATCH /server-port kaç kez denensin */
  'port_v2_quick_patch_tries' => 12,
  'tunnel_net_interface' => 'Ethernet0',
  /*
   * Çoklu tünel teslimi (sync queue veya uzun HTTP isteği): PHP max_execution_time / set_time_limit.
   * Job timeout (DeliverLocaltonetQueuedOrderJob) ile uyumlu tutun; 0 = sınırsız (önerilmez).
   */
  'delivery_max_execution_seconds' => 7200,
);