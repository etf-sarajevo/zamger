class CreateLmsProjectProjectStudents < ActiveRecord::Migration
  def change
    create_table :lms_project_project_students do |t|
      t.integer :student_id
      t.integer :project_id

      t.timestamps
    end
  end
end
