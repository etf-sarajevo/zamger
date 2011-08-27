class Core::Portfolio < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'student_predmet'
  # set_primary_key :student, :predmet
  # alias_attribute :student_id, :student
  # alias_attribute :course_offering_id, :predmet

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'student_predmet'
  # STUDENT_ID = TABLE_NAME + '.' + 'student'
  # COURSE_OFFERING_ID = TABLE_NAME + '.' + 'predmet'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_portfolios'
  STUDENT_ID = TABLE_NAME + '.' + 'student_id'
  COURSE_OFFERING_ID = TABLE_NAME + '.' + 'course_offering_id'

  ALL_COLUMNS = [STUDENT_ID, COURSE_OFFERING_ID]
  
  belongs_to :person, :foreign_key => "student_id"
  belongs_to :course_offering
  validates_presence_of :student_id, :course_offering_id
  
  
  
  def self.get_max_score(id)
    portfolio = (Core::Portfolio).find(id)
    
    max_score_queries = (Core::ScoringElement).joins(:scoring_element_scores).where((Core::ScoringElementScores)::STUDENT_ID => portfolio[:student_id], (Core::ScoringElementScores)::COURSE_OFFERING_ID => portfolio[:course_offering_id]).select((Core::ScoringElement)::ID, (Core::ScoringElement)::MAX, (Core::ScoringElement)::TYPE)
    
    max_score = 0
    # TODO Define constant for ScoringElement types, AND isn't homework type = 6?
    max_score_queries.each do |max_score_query|
      if (max_score_query[:type] == 4)
        max_score += ((Lms::Homework::Homework).joins("INNER JOIN " + (Core::CourseOffering)::TABLE_NAME + " ON " + (Lms::Homework::Homework)::COURSE_UNIT_ID + "=" + (Core::CourseOffering)::COURSE_UNIT_ID).where((Lms::Homework::Homework)::ACADEMIC_YEAR_ID + " = " + (Core::CourseOffering)::ACADEMIC_YEAR_ID, (Core::Homework::Homework).SCORING_ELEMENT_ID + "=" + max_score_type[:id].to_s).sum((Core::Homework::Homework)::SCORE)).round(2)
      else
        max_score += max_score_query[:max]
      end
    end
    
    return max_score
  end  
  
  def self.get_latest_grades_for_student(student_id, limit)
    select_columns = (Core::FinalGrade)::ALL_COLUMNS | [(Core::CourseUnit)::NAME]
    
    latest_grades = (Core::FinalGrade).includes(:course_unit).where(:student_id => student_id).where((Core::FinalGrade)::DATE + ">?", "#{1.month.ago}").order(:date + " DESC").limit(limit).select(select_columns)
    
    return latest_grades
  end
  
  
  def self.get_current_for_student(student_id)
    select_columns = [(Core::CourseOffering)::SEMESTER, (Core::CourseOffering)::ID, (Core::AcademicYear)::ID, (Core::CourseUnit)::NAME, (Core::CourseUnit)::SHORT_NAME]
    
    portfolio = (Core::Portfolio).includes(:course_offering => [:academic_year, :course_unit]).where((Core::Portfolio)::STUDENT_ID => student_id, (Core::AcademicYear)::CURRENT => true).select(select_columns).order((Core::CourseOffering)::SEMESTER + " DESC", (Core::CourseUnit)::NAME).first
    
    return portfolio
  end
  
  
  def self.from_course_offering(student_id, course_offering_id)
    portfolio = (Core::Portfolio).where(:student_id => student_id, :course_offering_id => course_offering_id).count
    
    return portfolio
  end
  
  
  def self.from_course_unit(student_id, course_unit_id, academic_year_id)
    portfolio = (Core::Portfolio).joins(:course_offering).where(:student_id => student_id, :course_offering_id => course_unit_id, :course_offering => {:academic_year_id => academic_year_id})
    
    return portfolio
  end
  
  def self.get_grade(id)
    portfolio = (Core::Portfolio).find(id)
    grade = (Core::FinalGrade).where(:course_unit_id => portfolio[:course_unit_id], :student_id => portfolio[:student_id]).select(:grade, :date).first
    
    return grade
  end
  
  def self.set_grade(id, grade)
    portfolio = (Core::Portfolio).find(id)
    grade_r = (Core::FinalGrade).where(:course_unit_id => portfolio[:course_unit_id], :student_id => portfolio[:student_id]).first
    grade_r.grade = grade
    # TODO INSERT query in set method (POST)
    return grade_r.save
end
  
  def self.delete_grade(id)
    grade = (Core::Portfolio).get_grade(id)
    return grade.delete
  end
  
  
  def self.get_score(id, scoring_element_id)
    portfolio = (Core::Portfolio).find(id)
    score = (Core::ScoringElementScore).where(:student_id => portfolio[:student_id], :course_unit_id => portfolio[:course_unit_id], :scoring_element_id => scoring_element_id).select(:id, :score).first
    
    return score
  end
  
  
  def get_score(scoring_element_id)
    score = (Core::ScoringElementScore).where(:student_id => self[:student_id], :course_unit_id => self[:course_unit_id], :scoring_element_id => scoring_element_id).select(:id, :score).first
    
    return score
  end
  
  def self.set_score(id, scoring_element_id, score)
    score = (Core::Portfolio).get_score(id, scoring_element_id)
    score[:score] = score
    # TODO Insert statement inside a set function??
    return score.save
  end
  
  def set_score(scoring_element_id, score)
    score = get_score(scoring_element_id)
    score[:score] = score
    # TODO Insert statement inside a set function??
    return score.save
  end
  
  
  def self.delete_score(id, scoring_element_id)
    score = (Core::Portfolio).get_score(id, scoring_element_id)
    return score.delete
  end
  
  def self.get_total_score(id)
    portfolio = (Core::Portfolio).find(id)
    total_score = (Core::ScoringElementScore).where(:student_id => portfolio[:student_id], :course_unit_id => portfolio[:course_unit_id]).sum(:score)
    
    return total_score
  end
  
  def self.get_all_for_student(student_id)
    select_columns = [(Core::CourseOffering)::ID, (Core::CourseUnit)::ID, (Core::CourseOffering)::ACADEMIC_YEAR_ID, (Core::CourseUnit)::NAME, (Core::CourseUnit)::SHORT_NAME, (Core::CourseOffering)::PROGRAMME_ID, (Core::CourseOffering)::MANDATORY]
    
    all_portfolios = (Core::Portfolio).includes(:course_offering => :course_unit).where(:student_id => student_id).select(select_columns).order((Core::CourseOffering)::ACADEMIC_YEAR_ID, :semester, (Core::CourseUnit)::NAME)
    
    return all_portfolios
  end
  
end
