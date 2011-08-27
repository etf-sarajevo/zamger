class Lms::Attendance::Group < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'grupa'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_ godina
  # alias_attribute :virtual, :virtualna

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'grupa'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # VIRTUAL = TABLE_NAME + '.' + 'virtualna'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_attendance_groups'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  VIRTUAL = TABLE_NAME + '.' + 'virtual'

  ALL_COLUMNS = [ID, NAME, COURSE_UNIT_ID, ACADEMIC_YEAR_ID, VIRTUAL]
  
  belongs_to :course_unit, :class_name => "Core::CourseUnit"
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
  has_many :classes
  has_many :student_groups
  
  validates_presence_of :name, :academic_year_id, :course_unit_id
  
  
  def self.from_student_and_course(student_id, course_unit_id, academic_year_id)
    groups = (Lms::Attendance::Group).where(:course_unit_id => course_unit_id, :academic_year_id => academic_year_id, (Lms::Attendance::StudentGroup)::STUDENT_ID => student_id).joins(:student_groups)
    return groups
  end
  
  def self.is_member(id, student_id)
    member = {}
    student_group = (Lms::Attendance::StudentGroup).where(:group_id => id, :student_id => student_id)
    
    if (student_group.empty?)
      member["member"] = false
    else
      member["member"] = true
    end
    return member
  end
  
  def self.is_teacher(id, teacher_id)
    teacher = {}
    teacher_group = (Lms::Attendance::TeacherGroup).where(:group_id => id, :teacher_id => teacher_id)
    
    if (teacher_group.empty?)
      teacher["teacher"] = false
    else
      teacher["teacher"] = true
    end
    
    return teacher
  end
end
