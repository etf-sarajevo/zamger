class Lms::Exam::Exam < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'ispit'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :date, :datum
  # alias_attribute :published_date_time, :vrijemeobjave
  # alias_attribute :scoring_element_id, :komponenta

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'ispit'
  # ID = TABLE_NAME + '.' + 'id'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # DATE = TABLE_NAME + '.' + 'datum'
  # PUBLISHED_DATE_TIME = TABLE_NAME + '.' + 'vrijemeobjave'
  # SCORING_ELEMENT_ID = TABLE_NAME + '.' + 'komponenta'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_exam_exams'
  ID = TABLE_NAME + '.' + 'id'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  DATE = TABLE_NAME + '.' + 'date'
  PUBLISHED_DATE_TIME = TABLE_NAME + '.' + 'published_date_time'
  SCORING_ELEMENT_ID = TABLE_NAME + '.' + 'scoring_element_id'

  ALL_COLUMNS = [ID, COURSE_UNIT_ID, ACADEMIC_YEAR_ID, DATE, PUBLISHED_DATE_TIME, SCORING_ELEMENT_ID]
  
  belongs_to :course_unit, :class_name => "Core::CourseUnit"
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
  belongs_to :scoring_element, :class_name => "Core::ScoringElement"
  has_many :exam_results
 
  after_validation :set_published_time_to_now_if_not_present
    
  def self.from_id(id)
    exam = (Lms::Exam::Exam).where(:id => id).includes(:scoring_element).select((Lms::Exam::Exam)::ALL_COLUMNS | (Core::ScoringElement)::ALL_COLUMNS)
    return exam
  end
  
  
  def self.from_course(course_unit_id, academic_year_id, order = "date")
    if order == "date"
      order_by = [(Lms::Exam::Exam)::DATE, (Lms::Exam::Exam)::SCORING_ELEMENT_ID]
    else
      order by = [(Lms::Exam::Exam)::SCORING_ELEMENT_ID, (Lms::Exam::Exam)::DATE]
    end
    exams = (Lms::Exam::Exam).where(:course_unit_id => course_unit_id, :academic_year_id => academic_year_id).includes(:scoring_element).select((Lms::Exam::Exam)::ALL_COLUMNS | (Core::ScoringElement)::ALL_COLUMNS).order(order_by)
    
    return exams
  end
  
  
private

  def set_published_time_to_now_if_not_present
    if self.published_date_time == nil
      self.published_date_time = Time.new
    end 
  end
  
end
