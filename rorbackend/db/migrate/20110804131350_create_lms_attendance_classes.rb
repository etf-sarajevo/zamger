class CreateLmsAttendanceClasses < ActiveRecord::Migration
  def change
    create_table :lms_attendance_classes do |t|
      t.date :date  # Compatibility
      t.time :time
      t.integer :teacher_id
      t.integer :group_id
      t.integer :scoring_element_id

      # t.timestamps
    end
  end
end
