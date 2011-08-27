class CreateLmsExamExams < ActiveRecord::Migration
  def change
    create_table :lms_exam_exams do |t|
      t.integer :course_unit_id, :default => 0
      t.integer :academic_year_id, :default => 0
      t.date :date, :default => '0000-00-00'
      t.time :published_date_time  # Compatibility
      t.integer :scoring_element_id, :default => 0
      
      # t.timestamps
    end
  end
end
