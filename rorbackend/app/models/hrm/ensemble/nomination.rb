class Hrm::Ensemble::Nomination < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'izbor'
  # alias_attribute :person_id, :osoba
  # alias_attribute :rank_id, :zvanje
  # alias_attribute :date_named, :datum_izbora
  # alias_attribute :date_expired, :datum_isteka
  # alias_attribute :domain_id, :oblast
  # alias_attribute :subdomain_id, :podoblast
  # alias_attribute :part_time, :dopunski
  # alias_attribute :other_institution, :druga_institucija

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'izbor'
  # PERSON_ID = TABLE_NAME + '.' + 'osoba'
  # RANK_ID = TABLE_NAME + '.' + 'zvanje'
  # DATE_NAMED = TABLE_NAME + '.' + 'datum_izbora'
  # DATE_EXPIRED = TABLE_NAME + '.' + 'datum_isteka'
  # DOMAIN_ID = TABLE_NAME + '.' + 'oblast'
  # SUBDOMAIN_ID = TABLE_NAME + '.' + 'podoblast'
  # PART_TIME = TABLE_NAME + '.' + 'dopunski'
  # OTHER_INSTITUTION = TABLE_NAME + '.' + 'druga_institucija'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'hrm_ensemble_nominations'
  PERSON_ID = TABLE_NAME + '.' + 'person_id'
  RANK_ID = TABLE_NAME + '.' + 'rank_id'
  DATE_NAMED = TABLE_NAME + '.' + 'date_named'
  DATE_EXPIRED = TABLE_NAME + '.' + 'date_expired'
  DOMAIN_ID = TABLE_NAME + '.' + 'domain_id'
  SUBDOMAIN_ID = TABLE_NAME + '.' + 'subdomain_id'
  PART_TIME = TABLE_NAME + '.' + 'part_time'
  OTHER_INSTITUTION = TABLE_NAME + '.' + 'other_institution'

  ALL_COLUMNS = [PERSON_ID, RANK_ID, DATE_NAMED, DATE_EXPIRED, DOMAIN_ID, SUBDOMAIN_ID, PART_TIME, OTHER_INSTITUTION]
  
  
  belongs_to :person, :class_name => "Core::Person"
  belongs_to :rank
  belongs_to :domain
  belongs_to :subdomain
  
  
  def self.get_latest_for_person(person_id)
    select_columns = [(Hrm::Ensemble::Rank)::ID, (Hrm::Ensemble::Rank)::NAME, (Hrm::Ensemble::Rank)::TITLE, (Hrm::Ensemble::Nomination)::DATE_NAMED, (Hrm::Ensemble::Nomination)::DATE_EXPIRED]
    
    nomination = (Hrm::Ensemble::Nomination).joins(:rank).where(:person_id => person_id).select(select_columns).order(:date_named).first
    
    return nomination
  end
  
end
