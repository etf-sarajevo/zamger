class CreateLmsAttendanceGroups < ActiveRecord::Migration
  def change
    create_table :lms_attendance_groups do |t|
      t.string :name, :limit => 100
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.boolean :virtual

      # t.timestamps
    end
  end
end
