class CreateLmsHomeworkAssignments < ActiveRecord::Migration
  def change
    create_table :lms_homework_assignments do |t|
      t.integer :homework_id, :default => 0
      t.integer :assign_no, :default => 0
      t.integer :student_id, :default => 0
      t.integer :status, :default => 0, :limit => 4
      t.float :score, :default => 0
      t.text :compile_report
      t.time :time, :default => nil
      t.text :comment
      t.string :filename, :limit => 200
      t.integer :author_id

      # t.timestamps
    end
  end
end
