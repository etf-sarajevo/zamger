class CreateLmsAttendanceAttendances < ActiveRecord::Migration
  def change
    create_table :lms_attendance_attendances do |t|
      t.integer :student_id
      t.integer :class_id
      t.boolean :present, :default => false
      t.boolean :plus_minus, :default => false
      
      # t.timestamps
    end
  end
end
