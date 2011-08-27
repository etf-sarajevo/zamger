class CreateLmsAttendanceStudentsGroups < ActiveRecord::Migration
  def change
    create_table :lms_attendance_students_groups do |t|
      t.integer :student_id, :default => 0
      t.integer :group_id, :default => 0

      # t.timestamps
    end
  end
end
