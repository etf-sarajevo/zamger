class CreateCoreEnrollments < ActiveRecord::Migration
  def change
    create_table :core_enrollments do |t|
      t.integer :student_id
      t.integer :programme_id
      t.integer :semester, :limit => 4
      t.integer :academic_year_id
      t.integer :enrollment_type_id
      t.boolean :repeat, :default => false
      t.integer :curriculum_id, :default => 0
      t.integer :document_id, :default => 0

      # t.timestamps
    end
    
    add_index :core_enrollments, [:student_id, :programme_id, :semester, :academic_year_id], :unique => true, :name => "unique_key_student_programme_semester_academic_year"
  end
end
