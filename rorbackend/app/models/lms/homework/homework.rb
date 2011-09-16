class Lms::Homework::Homework < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'zadaca'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :nr_assignments, :zadataka
  # alias_attribute :score, :bodova
  # alias_attribute :deadline, :rok
  # alias_attribute :active, :aktivna
  # alias_attribute :programming_language_id, :programskijezik
  # alias_attribute :attachment, :attachment
  # alias_attribute :allowed_extensions, :dozvoljene_ekstenzije
  # alias_attribute :text, :postavka_zadace
  # alias_attribute :scoring_element_id, :komponenta
  # alias_attribute :published_date_time, :vrijemeobjave

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'zadaca'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # NR_ASSIGNMENTS =  TABLE_NAME + '.' + 'zadataka'
  # SCORE =  TABLE_NAME + '.' + 'bodova'
  # DEADLINE =  TABLE_NAME + '.' + 'rok'
  # ACTIVE =  TABLE_NAME + '.' + 'aktivna'
  # PROGRAMMING_LANGUAGE_ID =  TABLE_NAME + '.' + 'programskijezik'
  # ATTACHMENT =  TABLE_NAME + '.' + 'attachment'
  # ALLOWED_EXTENSIONS =  TABLE_NAME + '.' + 'dozvoljene_ekstenzije'
  # TEXT =  TABLE_NAME + '.' + 'postavka_zadace'
  # SCORING_ELEMENT_ID =  TABLE_NAME + '.' + 'komponenta'
  # PUBLISHED_DATE_TIME =  TABLE_NAME + '.' + 'vrijemobjave'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_homework_homeworks'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  NR_ASSIGNMENTS =  TABLE_NAME + '.' + 'nr_assignments'
  SCORE =  TABLE_NAME + '.' + 'score'
  DEADLINE =  TABLE_NAME + '.' + 'deadline'
  ACTIVE =  TABLE_NAME + '.' + 'active'
  PROGRAMMING_LANGUAGE_ID =  TABLE_NAME + '.' + 'programming_language_id'
  ATTACHMENT =  TABLE_NAME + '.' + 'attachment'
  ALLOWED_EXTENSIONS =  TABLE_NAME + '.' + 'allowed_extensions'
  TEXT =  TABLE_NAME + '.' + 'text'
  SCORING_ELEMENT_ID =  TABLE_NAME + '.' + 'scoring_element_id'
  PUBLISHED_DATE_TIME =  TABLE_NAME + '.' + 'published_date_time'

  ALL_COLUMNS = [ID, NAME, COURSE_UNIT_ID, ACADEMIC_YEAR_ID, NR_ASSIGNMENTS, SCORE, DEADLINE, ACTIVE, PROGRAMMING_LANGUAGE_ID, ATTACHMENT, ALLOWED_EXTENSIONS, TEXT, SCORING_ELEMENT_ID, PUBLISHED_DATE_TIME]
  
  belongs_to :course_unit, :class_name => "Core::CourseUnit"
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
  belongs_to :programming_language
  belongs_to :scoring_element, :class_name => "Core::ScoringElement"
  has_many :assignments
  
  validates_presence_of :name, :scoring_element_id
  
  before_save :set_published_date_time_to_now
  
  def self.from_id(id)
    select_columns = (Lms::Homework::Homework)::ALL_COLUMNS | [(Core::CourseUnit)::NAME]    
    homework = (Lms::Homework::Homework).where(:id => id).includes(:course_unit).select(select_columns)
    
    return homework
  end
  
  
  def self.get_latest_for_student(student_id, limit)
    select_columns = (Lms::Homework::Homework)::ALL_COLUMNS | [(Core::CourseUnit)::NAME]
    homeworks_pass = (Lms::Homework::Homework).includes(:course_unit => [{:course_offerings => :portfolios}]).where((Core::Portfolio)::STUDENT_ID => student_id).select(select_columns).order(:deadline)
    homeworks = []
    
    homeworks_pass.each do |homework|
      
      if !((Core::FinalGrade).where(:course_unit_id => homeworks[:course_unit_id], :student_id => student_id).where((Core::FinalGrade)::GRADE + '>=6').count > 0)
        
        homeworks << homework
      end
    end
    return homeworks
  end
  
  
  def self.get_reviewed_for_student(student_id, limit)
    select_columns = (Lms::Homework::Homework)::ALL_COLUMNS | [(Core::CourseUnit)::NAME]
    statuses = (Lms::Homework::Assignment)::STATUS + '!=1 AND ' + (Lms::Homework::Assignment)::STATUS + '!=4'
    homeworks_all = (Lms::Homework::Homework).joins(:assignments, :course_unit).where((Lms::Homework::Assignment)::STUDENT_ID => student_id).where(statuses).where([(Lms::Homework::Assignment)::TIME + '>?', 1.month.ago.to_s]).order((Lms::Homework::Assignment)::ID).limit(limit)
    
    homeworks_past = []
    homeworks = []
    
    homeworks_all.each do |homework|
      homeworks << homework if homeworks.include?(homework)
    end
    
    return homeworks
  end
  
  
  def self.from_course(course_unit_id, academic_year_id)
    homeworks = (Lms::Homework::Homework).where(:course_unit_id => course_unit_id, :academic_year_id => academic_year_id).order(:scoring_element_id, :name)
    
    return homeworks
  end
  
  def self.from_course_scoring_element(course_unit_id, academic_year_id)
    scoring_elements = (Core::ScoringElement).joins(:course_unit_type_scoring_elements).joins("INNER JOIN " + (Core::CourseUnitYear)::TABLE_NAME + " ON " + (Core::CourseUnitYear)::COURSE_UNIT_TYPE_ID + '=' + (Core::CourseUnitTypeScoringElement)::COURSE_UNIT_TYPE_ID).where((Core::CourseUnitYear)::ACADEMIC_YEAR_ID => academic_year_id, (Core::CourseUnitYear)::COURSE_UNIT_ID => course_unit_id, (Core::ScoringElement)::SCORING_ID => 4).order(:id)
    
    return scoring_elements
  end
  
  
  def self.update_score_for_student(student_id, course_unit_id, academic_year_id)
    portfolio = (Core::Portfolio).from_course_unit(student_id, course_unit_id, academic_year_id)
    all_homeworks = (Lms::Homework::Homework).from_course(course_unit_id, academic_year_id)
    
    total_score = []
    
    all_homeworks.each do |homework|
      for i in 1..homework[:nr_assignments]
        assignment = (Lms::Homework::Assignment).from_student_homework_number(author_id, homework_id, assign_no)
        # TODO Make hash for status codes
        if (assignment[:status] == 5 and assignment != nil)
          total_score[homework[:scoring_element_id]] += assignment[:score]
        end
      end
    end
    
    total_score.each do |scoring_element, score|
      portfolio.set_score(portfolio[:id], scoring_element, score)
    end
    
    return true
  end
  
  
  def self.get_score_from_course_unit(student_id, course_unit_id, academic_year_id, homework_id)
    score = (Core::ScoringElementScore).joins(:course_offering).where((Core::ScoringElementScore)::STUDENT_ID => student_id, (Core::CourseOffering)::COURSE_UNIT_ID => course_unit_id, (Core::CourseOffering)::ACADEMIC_YEAR_ID => academic_year_id, (Core::ScoringElementScore)::SCORING_ELEMENT_ID => homework_id)
    
    return score
  end
  
private
  def set_published_date_time_to_now
    self.published_date_time = Time.now
  end
  
end
