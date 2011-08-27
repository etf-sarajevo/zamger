class CreateLmsAttendanceTeacherGroups < ActiveRecord::Migration
  def change
    create_table :lms_attendance_teacher_groups do |t|
      t.integer :teacher_id
      t.integer :group_id
      
      # t.timestamps
    end
  end
end
