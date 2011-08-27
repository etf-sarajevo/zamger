class CreateCoreFinalGrades < ActiveRecord::Migration
  def change
    create_table :core_final_grades do |t|
      t.integer :student_id
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.integer :grade, :limit => 3
      t.time :date
      t.integer :document_id

      # t.timestamps
    end
  end
end
