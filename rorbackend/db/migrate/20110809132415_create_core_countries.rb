class CreateCoreCountries < ActiveRecord::Migration
  def change
    create_table :core_countries do |t|
      t.string :name, :limit => 30

      # t.timestamps
    end
  end
end
