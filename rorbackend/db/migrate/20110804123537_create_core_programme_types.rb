class CreateCoreProgrammeTypes < ActiveRecord::Migration
  def change
    create_table :core_programme_types do |t|
      t.string :name
      t.integer :cycle
      t.integer :duration
      t.boolean :accepts_students
      
      # t.timestamps
    end
  end
end
