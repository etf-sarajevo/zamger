class Core::Auth < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'auth'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :login, :login
  # alias_attribute :password, :password
  # alias_attribute :admin, :admin
  # alias_attribute :external_id, :external_id
  # alias_attribute :active, :aktivan

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'auth'
  # ID = TABLE_NAME + '.' + 'id'
  # LOGIN = TABLE_NAME + '.' + 'login'
  # PASSWORD = TABLE_NAME + '.' + 'password'
  # ADMIN = TABLE_NAME + '.' + 'admin'
  # EXTERNAL_ID = TABLE_NAME + '.' + 'external_id'
  # ACTIVE = TABLE_NAME + '.' + 'aktivan'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_auths'
  ID = TABLE_NAME + '.' + 'id'
  LOGIN = TABLE_NAME + '.' + 'login'
  PASSWORD = TABLE_NAME + '.' + 'password'
  ADMIN = TABLE_NAME + '.' + 'admin'
  EXTERNAL_ID = TABLE_NAME + '.' + 'external_id'
  ACTIVE = TABLE_NAME + '.' + 'active'

  ALL_COLUMNS = [ID, LOGIN, PASSWORD, ADMIN, EXTERNAL_ID, ACTIVE]
    
  belongs_to :person, :foreign_key => 'external_id'
end
