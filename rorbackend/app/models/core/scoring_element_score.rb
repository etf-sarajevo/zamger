class Core::ScoringElementScore < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'komponentebodovi'
  # set_primary_key :student, :predmet, :komponenta
  # alias_attribute :student_id, :student
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :scoring_element_id, :komponenta
  # alias_attribute :score, :bodovi

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'komponentebodovi'
  # STUDENT_ID = TABLE_NAME + '.' + 'student'
  # COURSE_OFFERING_ID = TABLE_NAME + '.' + 'predmet'
  # SCORING_ELEMENT_ID = TABLE_NAME + '.' + 'komponenta'
  # SCORE = TABLE_NAME + '.' + 'bodovi'
 
  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_scoring_element_scores'
  STUDENT_ID = TABLE_NAME + '.' + 'student_id'
  COURSE_OFFERING_ID = TABLE_NAME + '.' + 'course_offering_id'
  SCORING_ELEMENT_ID = TABLE_NAME + '.' + 'scoring_element_id'
  SCORE = TABLE_NAME + '.' + 'score'

  ALL_COLUMNS = [STUDENT_ID, COURSE_OFFERING_ID, SCORING_ELEMENT_ID, SCORE]
  
  belongs_to :student
  belongs_to :course_offering
  belongs_to :scoring_element
  
  validates_presence_of :score, :student_id, :course_offering_id, :scoring_element_id
  
  def self.get_score_from_course_unit(student_id, course_unit_id, academic_year_id, attendance_id)
    score1 = (Core::ScoringElementScore).joins(:course_offering).where((Core::ScoringElementScore)::STUDENT_ID => student_id, (Core::CourseOffering)::COURSE_UNIT_ID => course_unit_id, (Core::CourseOffering)::ACADEMIC_YEAR_ID => academic_year_id, (Core::ScoringElementScore)::SCORING_ELEMENT_ID => attendance_id).sum(:score)
    score = (Core::ScoringElementScore).new(:score => score1)
    return score
  end
  
end
