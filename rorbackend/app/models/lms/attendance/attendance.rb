class Lms::Attendance::Attendance < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'prisutan'
  # set_primary_key :student, :cas
  # alias_attribute :student_id, :student
  # alias_attribute :class_id, :cas
  # alias_attribute :present, :prisutan
  # alias_attribute :plus_minus, :plus_minus

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'prisutan'
  # STUDENT_ID = TABLE_NAME + '.' + 'student'
  # CLASS_ID = TABLE_NAME + '.' + 'cas'
  # PRESENT = TABLE_NAME + '.' + 'prisutan'
  # PLUS_MINUS = TABLE_NAME + '.' + 'plus_minus'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_attendance_attendances'
  STUDENT_ID = TABLE_NAME + '.' + 'student_id'
  CLASS_ID = TABLE_NAME + '.' + 'class_id'
  PRESENT = TABLE_NAME + '.' + 'present'
  PLUS_MINUS = TABLE_NAME + '.' + 'plus_minus'

  ALL_COLUMNS = [STUDENT_ID, CLASS_ID, PRESENT, PLUS_MINUS]
  
  belongs_to :student, :class_name => "Core::Person"
  belongs_to :zclass, :foreign_key => "class_id", :class_name => "Lms::Attendance::Class"
 
  validates_presence_of :student_id, :class_id
  
  
  def self.from_student_and_class(student_id, class_id)
    attendance = (Lms::Attendance::Attendance).where(:student_id => student_id, :class_id => class_id).select(:present).first
    return attendance
  end
  
  
  def self.get_presence(id)
    attendance = (Lms::Attendance::Attendance).where(:id => id).select(:present).first
    return attendance
  end
  
  
  def self.set_presence(id, present)
    attendance = (Lms::Attendance::Attendance).find(id)
    attendance.presence = present
    # TODO How can this method know which class and student it belongs from parameters?
    return attendance.save
  end
  
  
  def self.update_score(student_id, scoring_element_id, course_unit_id, academic_year_id)
    scoring_element = (Core::ScoringElement).find(scoring_element_id)
    portfolio = (Core::Portfolio).from_course_unit(student_id, course_unit_id, academic_year_id)
    score = (Lms::Attendance::Attendance).joins(:class => :group).where(:student_id => student_id, (Lms::Attendance::Group).TABLE_NAME => {:academic_year_id => academic_year_id, :course_unit_id => course_unit_id}).count
    
    if score > scoring_element[:option]
      return (Core::Portfolio).set_score(portfolio[:id], scoring_element_id, score)
    else
      return (Core::Portfolio).set_score(portfolio[:id], scoring_element_id, scoring_element[:max])
    end
  end
  
  
  def self.from_course_unit(course_unit_id, academic_year_id)
    attendances = (Core::ScoringElement).joins(:course_unit_type_scoring_elements).joins("INNER JOIN " + (Core::CourseUnitYear)::TABLE_NAME + " ON " + (Core::CourseUnitYear)::COURSE_UNIT_TYPE_ID + '=' + (Core::CourseUnitTypeScoringElement)::COURSE_UNIT_TYPE_ID).where((Core::CourseUnitYear)::COURSE_UNIT_ID => course_unit_id, (Core::CourseUnitYear)::ACADEMIC_YEAR_ID => academic_year_id, :scoring_id => 3).order(:id)
    
    return attendances
  end
  
  
end
