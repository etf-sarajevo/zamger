class Core::Scoring < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'tipkomponente'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :options_description, :opis_opcija

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'tipkomponente'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # OPTIONS_DESCRIPTION = TABLE_NAME + '.' + 'opis_opcija'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_scorings'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  OPTIONS_DESCRIPTION = TABLE_NAME + '.' + 'options_description'

  ALL_COLUMNS = [ID, NAME, OPTIONS_DESCRIPTION]
  
  has_many :scoring_elements
  
  validates_presence_of :name, :options_description
  
  
  def self.get_scoring_elements(id, se_type)
    condition = '1=1'
    if (!(se_type == nil) and !(se_type == '0'))
      condition = (Core::ScoringElement)::SCORING_ELEMENT_TYPE + '=' + se_type
    end
    scoring_elements = (Core::Scoring).joins(:course_unit_type_scoring_elements).where((Core::CourseUnitTypeScoringElement)::COURSE_UNIT_ID => id).where(condition)
    
    return scoring_elements
  end
  
end
