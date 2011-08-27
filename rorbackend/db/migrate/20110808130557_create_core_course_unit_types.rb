class CreateCoreCourseUnitTypes < ActiveRecord::Migration
  def change
    create_table :core_course_unit_types do |t|
      t.string :name, :limit => 50

      # t.timestamps
    end
  end
end
