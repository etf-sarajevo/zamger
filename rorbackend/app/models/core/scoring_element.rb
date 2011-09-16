class Core::ScoringElement < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'komponenta'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :gui_name, :gui_naziv
  # alias_attribute :short_gui_name, :kratki_gui_naziv
  # alias_attribute :scoring_id, :tipkomponente
  # alias_attribute :max, :maxbodova
  # alias_attribute :pass, :prolaz
  # alias_attribute :option, :opcija
  # alias_attribute :mandatory, :uslov

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'komponenta'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # GUI_NAME = TABLE_NAME + '.' + 'gui_naziv'
  # SHORT_GUI_NAME = TABLE_NAME + '.' + 'kratki_gui_naziv'
  # SCORING_ID = TABLE_NAME + '.' + 'tipkomponente'
  # MAX = TABLE_NAME + '.' + 'maxbodova'
  # PASS = TABLE_NAME + '.' + 'prolaz'
  # OPTION = TABLE_NAME + '.' + 'opcija'
  # MANDATORY = TABLE_NAME + '.' + 'uslov'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_scoring_elements'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  GUI_NAME = TABLE_NAME + '.' + 'gui_name'
  SHORT_GUI_NAME = TABLE_NAME + '.' + 'short_gui_name'
  SCORING_ID = TABLE_NAME + '.' + 'scoring_id'
  MAX = TABLE_NAME + '.' + 'max'
  PASS = TABLE_NAME + '.' + 'pass'
  OPTION = TABLE_NAME + '.' + 'option'
  MANDATORY = TABLE_NAME + '.' + 'mandatory'

  ALL_COLUMNS = [ID, NAME, GUI_NAME, SHORT_GUI_NAME, SCORING_ID, MAX, PASS, OPTION, MANDATORY]
  
  
  has_many :exams, :class_name => "Lms::Exam::Exam"
  belongs_to :scoring
  has_many :scoring_element_scores
  has_many :course_unit_type_scoring_elements
  
  validates_presence_of :name, :gui_name, :short_gui_name, :scoring_id, :max, :pass, :option, :mandatory
  
  def self.from_course_unit_except_exams(course_unit_id, academic_year_id)
    scoring_elements = (Core::ScoringElement).joins(:course_unit_type_scoring_elements).joins("INNER JOIN " + (Core::CourseUnitYear)::TABLE_NAME + " ON " + (Core::CourseUnitYear)::COURSE_UNIT_TYPE_ID + '=' + (Core::CourseUnitTypeScoringElement)::COURSE_UNIT_TYPE_ID).where((Core::CourseUnitYear)::COURSE_UNIT_ID + '=' + course_unit_id.to_s).where((Core::ScoringElement)::SCORING_ID + '<> 1 AND ' + (Core::ScoringElement)::SCORING_ID + '<> 2').where((Core::CourseUnitYear)::ACADEMIC_YEAR_ID => academic_year_id)
    
    return scoring_elements
  end
  
end
