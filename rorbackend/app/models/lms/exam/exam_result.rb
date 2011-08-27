class Lms::Exam::ExamResult < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'ispitocjene'
  # set_primary_key :ispit, :student
  # alias_attribute :student_id, :student
  # alias_attribute :exam_id, :ispit
  # alias_attribute :result, :ocjena

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'ispitocjene'
  # STUDENT_ID = TABLE_NAME + '.' + 'student'
  # EXAM_ID = TABLE_NAME + '.' + 'ispit'
  # RESULT = TABLE_NAME + '.' + 'ocjena'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_exam_exam_results'
  STUDENT_ID = TABLE_NAME + '.' + 'student_id'
  EXAM_ID = TABLE_NAME + '.' + 'exam_id'
  RESULT = TABLE_NAME + '.' + 'result'

  ALL_COLUMNS = [STUDENT_ID, EXAM_ID, RESULT]
  
  belongs_to :student, :class_name => "Core::Person"
  belongs_to :exam
  
  validate :result_in_bounds


  def self.from_student_and_exam(student_id, exam_id)
    exam_result = (Lms::Exam::ExamResult).where(:student_id => student_id, :exam_id => exam_id).select([:result])
    
    return exam_result
  end
  
  def self.set_exam_result(id, result)
    exam_result = (Lms::Exam::ExamResult).find(id)
    exam_result[:result] = result
    # TODO How can the method know which student and exam it belongs, create inside a setter
    return exam_result.save
  end
  
  def self.delete_exam_result
    return true if (Lms::Exam::ExamResult).delete(id) > 0
    return false
  end
  
  
  def self.update_scoring(exam_id, student_id)
    exam = (Lms::Exam::Exam).find(exam_id)
    
    raise ActiveRecord::RecordNotFound if exam == nil
    
    portfolio = (Core::Portfolio).from_course_unit(student_id, exam[:course_unit_id], exam[:academic_year_id])
    
    raise ActiveRecord::RecordNotFound if portfolio == nil
    
    scoring_element = (Core::ScoringElement).joins(:course_unit_type_scoring_elements, " INNER JOIN " + (Core::CourseUnitYear)::TABLE_NAME + " ON " + (Core::CourseUnitTypeScoringElement)::COURSE_UNIT_TYPE_ID + '=' + (Core::CourseUnitYear)::COURSE_UNIT_TYPE_ID).where((Core::CourseUnitYear)::COURSE_UNIT_ID => exam[:course_unit_id], (Core::CourseUnitYear)::ACADEMIC_YEAR_ID => exam[:academic_year_id]).where((Core::ScoringElement)::OPTION + " like ?", "%#{exam[:scoring_element_id]}%").select((Core::ScoringElement)::ID, (Core::ScoringElement)::OPTION, (Core::ScoringElement)::PASS).first
    
    raise ActiveRecord::RecordNotFound if scoring_element == nil
    
    exam_result = (Lms::Exam::ExamResult).joins(:exam).where((Lms::Exam::Exam)::TABLE_NAME => {:academic_year_id => exam[:academic_year_id], :scoring_element_id => scoring_element[:id]}, :student_id => student_id).select(:result).order(:result).first
    raise ActiveRecord::RecordNotFound if exam_result == nil
    
    partials = scoring_element_id[:option].split['+']
    sum = 0
    pass_exam = 1
    partials_score = {}
    partials.each do |partial|
      pass = (Core::ScoringElement).where(:id => partial).select(:pass).first
      result = (Lms::Exam::ExamResult).joins(:exam).where((Lms::Exam::Exam)::COURSE_UNIT_ID => exam[:course_unit_id], (Lms::Exam::Exam)::ACADEMIC_YEAR_ID => exam[:academic_year_id], (Lms::Exam::Exam)::SCORING_ELEMENT_ID => exam[:scoring_element_id], :student_id => student_id).select(:result).order(:result).first
      
      if result[:result] != nil
        partials_score[partial] = result[:result]
        pass_exam = 0 if result[:result] < pass[:pass]
        sum += result[:result]
      else
        pass_exam = 0
      end
      
      if sum > exam_result[:result] or (pass_exam == 0 and exam_result[:result] > exam[:pass])
        partials.each do |partial|
          (Core::Portfolio).delete_score(portfolio[:id], scoring_element[:id])
        end
        (Core::Portfolio).set_score(portfolio[:id], scoring_element[:id], exam_result[:result])
        
      else
        partials.each do |partial|
          (Core::Portfolio).set_score(portfolio[:id], scoring_element[:id], partial)
        end
        (Core::Portfolio).delete_score(portfolio[:id], scoring_element[:id])
      end
    end
    return true
  end
  
  
  def self.get_latest_for_student(student_id, limit)
    exam_results = (Lms::Exam::ExamResult).joins(:exam).where(:student_id => student).order((Lms::Exam::Exam)::PUBLISHED_DATE_TIME + " DESC").limit(limit)
    
    return exam_results
  end
  
  
private
  def result_in_bounds
    result_bounds = (Lms::Exam::Exam).where(:id => self.exam_id).includes(:scoring_element).select([:max]).first
    if (self.result.to_f > result_bounds[:max] or self.result < 0)
      errors.add(:result, 'Result must be within allowed range: [0, ' + result_bounds[:max].to_s + '].')
    end
  end
end
