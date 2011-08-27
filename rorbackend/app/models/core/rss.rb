class Core::Rss < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'rss'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute : auth_id, :auth
  # alias_attribute :accessed_at, :access

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'rss'
  # ID = TABLE_NAME + '.' + 'id'
  # AUTH_ID = TABLE_NAME + '.' + 'auth'
  # ACCESSED_AT = TABLE_NAME + '.' + 'access'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_rsses'
  ID = TABLE_NAME + '.' + 'id'
  AUTH_ID = TABLE_NAME + '.' + 'auth_id'
  ACCESSED_AT = TABLE_NAME + '.' + 'accessed_at'

  ALL_COLUMNS = [ID, AUTH_ID, ACCESSED_AT]
  
  belongs_to :auth
  
  
  after_find :update_timestamp
  
  
  def self.from_person_id(person_id)
    begin
      rss = (Core::Rss).where(:auth_id => person_id).first
    rescue ActiveRecord::RecordNotFound
      # TODO Inserting data in getter?
    end
    
    return rss
  end
  
  def self.update_timestamp(id)
    rss = (Core::Rss).find(id)
    return rss.accessed_at = Time.new
    return rss.save
  end
end
