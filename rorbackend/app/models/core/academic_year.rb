class Core::AcademicYear < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'akademska_godina'
  # set_primary_key :id
  # alias_attribute :id, :id
  # ealias_attribute :name, :naziv
  # alias_attribute :current, :aktuelna

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'akademska_godina
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # CURRENT = TABLE_NAME + '.' + 'aktuelna'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'core_academic_years'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  CURRENT = TABLE_NAME + '.' + 'current'

  ALL_COLUMNS = [ID, NAME, CURRENT]
    
  has_many :course_unit_years
  has_many :course_offerings
  has_many :enrollments
  has_many :groups, :class_name => "Lms::Attendance::Group"
  has_many :exams, :class_name => "Lms::Exam::Exam"
  
  validates_presence_of :name, :current
  validates_length_of :name, :maximum => 20

  before_create :assure_one_current_year
  before_update :assure_one_current_year
  
  
  def self.current_year
    current = (Core::AcademicYear).where(:current => true).limit(1).first
    return current
  end
  
  
  def self.set_as_current(id)
    current_year = (Core::AcademicYear).find(params[:id])    
    current.current = true
    return current.save
  end
  
  
  
private
  # Assures that there is only one current year before create and before update 
  def assure_one_current_year
    if self[:current] == true
      @current = nil
      begin
        @current = (Core::AcademicYear).where(:current => true)
      rescue ActiveRecord::RecordNotFound
      end
      
      if (@current!=nil)
        @current.each do |academic_year|
        academic_year.current = false
        academic_year.save
        end
      end
    end
  end 
end