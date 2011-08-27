module Lms::Moodle
  MOODLE = true
  
  # URL do početne Moodle stranice (bez index.php i  slično, samo direktorij, obavezno kosa crta na kraju)
  URL = 'http://c2.etf.unsa.ba/'
  
  # MySQL baza u kojoj se nalaze moodle tabele
  DB = 'moodle2'
  
  # Prefiks moodle tabela. U default Moodle instalaciji to je "mdl_"
  DB_PREFIX = 'mdl_'  
  
  # Ako se Moodle baza nalazi na istom MySQL serveru kao i Zamger i isti korisnik ima SELECT privilegije nad tim  tabelama, postavite vrijednost ispod na true, u suprotnom koristite false
  REUSE_CONNECTION = true
  
  # Ako je gornja vrijednost bila false, podesite ostale parametre pristupa Moodle bazi (naziv baze je $conf_moodle_db iznad) - parametri se nalaze u config/database.yml datoteci
  
  def self.table_name_prefix
    'lms_moodle_'
  end
end
