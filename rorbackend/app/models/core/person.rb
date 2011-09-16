class Core::Person < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'osoba'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :ime
  # alias_attribute :surname, :prezime
  # alias_attribute :fathers_name, :imeoca
  # alias_attribute :fathers_surname, :prezimeoca
  # alias_attribute :mothers_name, :imemajke
  # alias_attribute :mothers_surname, :prezimemajke
  # alias_attribute :gender, :spol
  # alias_attribute :email, :email
  # alias_attribute :student_id_number, :brindexa
  # alias_attribute :date_of_birth, :datum_rodjenja
  # alias_attribute :place_of_birth_id, :mjesto_rodjenja
  # alias_attribute :ethnicity_id, :nacionalnost
  # alias_attribute :nationality_id, :drzavljanstvo
  # alias_attribute :soldier_category, :boracke_kategorije
  # alias_attribute :personal_id_number, :jmbg
  # alias_attribute :address, :adresa
  # alias_attribute :address_place_id, :adresa_mjesto
  # alias_attribute :phone, :telefon
  # alias_attribute :canton_id, :kanton
  # alias_attribute :for_delete, :treba_brisati
  # alias_attribute :professional_level_id, :strucni_stepen
  # alias_attribute :science_level_id, :naucni_stepen
  # alias_attribute :picture, :slika

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'osoba'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # SURNAME = TABLE_NAME + '.' + 'prezime'
  # FATHERS_NAME = TABLE_NAME + '.' + 'imeoca'
  # FATHERS_SURNAME = TABLE_NAME + '.' + 'prezimeoca'
  # MOTHERS_NAME = TABLE_NAME + '.' + 'imemajke'
  # MOTHERS_SURNAME = TABLE_NAME + '.' + 'prezimemajke'
  # GENDER = TABLE_NAME + '.' + 'spol'
  # EMAIL = TABLE_NAME + '.' + 'email'
  # STUDENT_ID_NUMBER = TABLE_NAME + '.' + ' brindexa'
  # DATE_OF_BIRTH = TABLE_NAME + '.' + 'datum_rodjenja'
  # PLACE_OF_BIRTH_ID = TABLE_NAME + '.' + 'mjesto_rodjenja'
  # ETHNICITY_ID = TABLE_NAME + '.' + 'nacionalnost'
  # NATIONALITY_ID = TABLE_NAME + '.' + 'drzavljanstvo'
  # SOLDIER_CATEGORY = TABLE_NAME + '.' + 'boracke_kategorije'
  # PERSONAL_ID_NUMBER = TABLE_NAME + '.' + 'jmbg'
  # ADDRESS = TABLE_NAME + '.' + 'adresa'
  # ADDRESS_PLACE_ID = TABLE_NAME + '.' + 'adresa_mjesto'
  # PHONE = TABLE_NAME + '.' + 'telefon'
  # CANTON_ID = TABLE_NAME + '.' + 'kanton'
  # FOR_DELETE = TABLE_NAME + '.' + 'treba_brisati'
  # PROFESSIONAL_LEVEL_ID = TABLE_NAME + '.' + 'strucni_stepen'
  # SCIENCE_LEVEL_ID = TABLE_NAME + '.' + 'naucni_stepen'
  # PICTURE = TABLE_NAME + '.' + 'slika'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_people'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  SURNAME = TABLE_NAME + '.' + 'surname'
  FATHERS_NAME = TABLE_NAME + '.' + 'fathers_name'
  FATHERS_SURNAME = TABLE_NAME + '.' + ' fathers_surname'
  MOTHERS_NAME = TABLE_NAME + '.' + 'mothers_name'
  MOTHERS_SURNAME = TABLE_NAME + '.' + 'mothers_surname'
  GENDER = TABLE_NAME + '.' + 'gender'
  EMAIL = TABLE_NAME + '.' + 'email'
  STUDENT_ID_NUMBER = TABLE_NAME + '.' + 'student_id_number'
  DATE_OF_BIRTH = TABLE_NAME + '.' + 'date_of_birth'
  PLACE_OF_BIRTH_ID = TABLE_NAME + '.' + 'place_of_birth_id'
  ETHNICITY_ID = TABLE_NAME + '.' + 'ethnicity_id'
  NATIONALITY_ID = TABLE_NAME + '.' + 'nationality_id'
  SOLDIER_CATEGORY = TABLE_NAME + '.' + 'soldier_category'
  PERSONAL_ID_NUMBER = TABLE_NAME + '.' + 'personal_id_number'
  ADDRESS = TABLE_NAME + '.' + 'address'
  ADDRESS_PLACE_ID = TABLE_NAME + '.' + 'address_place_id'
  PHONE = TABLE_NAME + '.' + 'phone'
  CANTON_ID = TABLE_NAME + '.' + 'canton_id'
  FOR_DELETE = TABLE_NAME + '.' + 'for_delete'
  PROFESSIONAL_LEVEL_ID = TABLE_NAME + '.' + 'professional_level_id'
  SCIENCE_LEVEL_ID = TABLE_NAME + '.' + 'science_level_id'
  PICTURE = TABLE_NAME + '.' + 'picture'

  ALL_COLUMNS = [ID, NAME, SURNAME, FATHERS_NAME, FATHERS_SURNAME, MOTHERS_NAME, MOTHERS_SURNAME, GENDER, EMAIL, STUDENT_ID_NUMBER, DATE_OF_BIRTH, PLACE_OF_BIRTH_ID, ETHNICITY_ID, NATIONALITY_ID, SOLDIER_CATEGORY, PERSONAL_ID_NUMBER, ADDRESS, ADDRESS_PLACE_ID, PHONE, CANTON_ID, FOR_DELETE, PROFESSIONAL_LEVEL_ID, SCIENCE_LEVEL_ID, PICTURE]
  
  has_many :portfolios, :foreign_key => :student_id
  has_many :attendances, :class_name => "Lms::Attendance::Attendance"
  has_many :messages_sent, :class_name => "Common::Pm::Message", :foreign_key => "from_id"
  has_many :messages_inbox, :class_name => "Common::Pm::Message", :foreign_key => "to_id"
  belongs_to :place_of_birth, :class_name => "Core::Place"
  belongs_to :ethnicity
  belongs_to :nationality, :class_name => "Core::Country"
  belongs_to :address_place, :class_name => "Core::Place" 
  belongs_to :canton
  belongs_to :professional_level
  belongs_to :science_level
  belongs_to :auth, :foreign_key => 'external_id'
  validates_presence_of :name, :surname, :fathers_name, :fathers_surname, :mothers_name, :mothers_surname, :gender, :email, :student_id_number, :date_of_birth, :place_of_birth_id, :ethnicity_id, :nationality_id, :soldier_category, :personal_id_number, :address, :address_place_id, :phone, :canton_id, :professional_level_id, :science_level_id, :picture
  
  def self.search(query)
    search_string = query.downcase
    
    result = (Core::Person).find_by_sql(["SELECT #{(Core::Person)::ALL_COLUMNS} FROM #{(Core::Person)::TABLE_NAME} inner join #{(Core::Auth)::TABLE_NAME} #{(Core::Auth)::EXTERNAL_ID} = #{(Core::Person)::ID} WHERE (lower(#{(Core::Person)::NAME}) LIKE ? OR lower(#{(Core::Person)::SURNAME}) LIKE ? OR lower(#{(Core::Person)::STUDENT_ID_NUMBER}) LIKE ? OR lower(#{(Core::Auth)::LOGIN}) LIKE ?)", "%#{search_string}%", "%#{search_string}%", "%#{search_string}%", "%#{search_string}%"])
    
    return result
  end
end
