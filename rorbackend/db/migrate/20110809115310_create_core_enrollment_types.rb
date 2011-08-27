class CreateCoreEnrollmentTypes < ActiveRecord::Migration
  def change
    create_table :core_enrollment_types do |t|
      t.string :name, :limit => 30

      # t.timestamps
    end
  end
end
